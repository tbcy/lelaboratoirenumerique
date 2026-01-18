<?php

namespace App\Services\Mcp\Resources;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\Note;
use App\Services\DocumentExtractionService;
use App\Services\Mcp\PaginationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class NoteResource
{
    public function __construct(
        private PaginationService $pagination,
        private DocumentExtractionService $documentExtractor
    ) {}

    public function listResources(): array
    {
        return [
            [
                'uri' => 'labo://notes',
                'name' => 'Notes',
                'description' => 'List and read notes (hierarchical pages)',
                'mimeType' => 'application/json',
            ],
        ];
    }

    public function list(array $filters = []): array
    {
        $query = Note::query()
            ->with(['stakeholders', 'scopes', 'parent'])
            ->withCount('children')
            ->orderBy('datetime', 'desc');

        // Filter by parent
        if (isset($filters['parent_id'])) {
            if ($filters['parent_id'] === null || $filters['parent_id'] === 'null') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $filters['parent_id']);
            }
        }

        // Filter root only
        if (! empty($filters['root_only'])) {
            $query->whereNull('parent_id');
        }

        // Filter by scope
        if (! empty($filters['scope_id'])) {
            $query->whereHas('scopes', fn ($q) => $q->where('note_scopes.id', $filters['scope_id']));
        }

        // Filter by stakeholder
        if (! empty($filters['stakeholder_id'])) {
            $query->whereHas('stakeholders', fn ($q) => $q->where('stakeholders.id', $filters['stakeholder_id']));
        }

        // Simple search in name
        if (! empty($filters['search'])) {
            $query->where('name', 'LIKE', '%'.$filters['search'].'%');
        }

        $result = $this->pagination->paginate($query, $filters);

        return [
            'notes' => collect($result['data'])->map(fn ($note) => $this->transformNote($note))->toArray(),
            'pagination' => $result['pagination'],
        ];
    }

    public function get(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'error' => 'Note ID is required',
            ];
        }

        try {
            $note = Note::with(['stakeholders', 'scopes', 'parent', 'children'])
                ->withCount('children')
                ->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('note', $id);
        }

        return $this->transformNote($note, true);
    }

    public function getChildren(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'error' => 'Note ID is required',
            ];
        }

        try {
            $note = Note::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('note', $id);
        }

        $children = $note->children()
            ->with(['stakeholders', 'scopes'])
            ->withCount('children')
            ->orderBy('sort_order')
            ->orderBy('datetime', 'desc')
            ->get();

        return [
            'parent_id' => $id,
            'parent_name' => $note->name,
            'children' => $children->map(fn ($child) => $this->transformNote($child))->toArray(),
        ];
    }

    public function getTree(array $args): array
    {
        $rootId = $args['root_id'] ?? null;

        if ($rootId) {
            try {
                $root = Note::with(['stakeholders', 'scopes'])
                    ->withCount('children')
                    ->findOrFail($rootId);
            } catch (ModelNotFoundException $e) {
                throw ResourceNotFoundException::make('note', $rootId);
            }

            return [
                'tree' => [$this->buildTreeNode($root)],
            ];
        }

        // Get all root notes
        $roots = Note::whereNull('parent_id')
            ->with(['stakeholders', 'scopes'])
            ->withCount('children')
            ->orderBy('datetime', 'desc')
            ->get();

        return [
            'tree' => $roots->map(fn ($root) => $this->buildTreeNode($root))->toArray(),
        ];
    }

    /**
     * Full-text search across all note content fields using FTS5
     * Provides 10-100x faster search with BM25 relevance ranking
     */
    public function search(array $args): array
    {
        $queryStr = $args['query'] ?? '';

        if (empty($queryStr)) {
            return [
                'success' => false,
                'error' => 'Search query is required',
            ];
        }

        // Sanitize and prepare query for FTS5
        $ftsQuery = $this->sanitizeFtsQuery($queryStr);

        // Use FTS5 with BM25 ranking
        // Weights: name=1.0, short_summary=0.75, long_summary=0.75, notes=0.5, transcription=0.5
        $query = Note::query()
            ->select('notes.*')
            ->selectRaw('bm25(notes_fts, 1.0, 0.75, 0.75, 0.5, 0.5) as relevance')
            ->join('notes_fts', 'notes.id', '=', 'notes_fts.rowid')
            ->whereRaw('notes_fts MATCH ?', [$ftsQuery])
            ->with(['stakeholders', 'scopes', 'parent'])
            ->withCount('children');

        // Filter by scope
        if (! empty($args['scope_id'])) {
            $query->whereHas('scopes', fn ($q) => $q->where('note_scopes.id', $args['scope_id']));
        }

        // Filter by stakeholder
        if (! empty($args['stakeholder_id'])) {
            $query->whereHas('stakeholders', fn ($q) => $q->where('stakeholders.id', $args['stakeholder_id']));
        }

        // Filter by date range
        if (! empty($args['date_from'])) {
            $query->where('notes.datetime', '>=', $args['date_from']);
        }
        if (! empty($args['date_to'])) {
            $query->where('notes.datetime', '<=', $args['date_to']);
        }

        // Order by relevance (BM25 score - lower is better)
        $notes = $query->orderBy('relevance')->limit(50)->get();

        return [
            'query' => $queryStr,
            'results_count' => $notes->count(),
            'notes' => $notes->map(fn ($note) => [
                ...$this->transformNote($note),
                'relevance_score' => $note->relevance ? round(abs($note->relevance), 4) : null,
                'match_context' => $this->extractContext($note, $queryStr),
            ])->toArray(),
        ];
    }

    /**
     * Sanitize query string for FTS5
     * Handles phrase search (quoted), escapes special characters
     */
    private function sanitizeFtsQuery(string $query): string
    {
        $query = trim($query);

        // If already quoted (phrase search), return as-is
        if (preg_match('/^".*"$/', $query)) {
            return $query;
        }

        // Escape special FTS5 characters and operators
        $query = str_replace(['"', "'", '(', ')', '*', '-'], ' ', $query);

        // Split into terms
        $terms = array_filter(explode(' ', $query), fn ($t) => strlen($t) >= 2);

        if (empty($terms)) {
            return '""'; // Empty query fallback
        }

        // Create FTS5 query with prefix matching (term*)
        // Use OR to match any term
        return implode(' OR ', array_map(fn ($t) => '"' . $t . '"*', $terms));
    }

    public function read(): array
    {
        return $this->list();
    }

    public function readSingle(int $id): array
    {
        return $this->get(['id' => $id]);
    }

    private function transformNote(Note $note, bool $includeContent = false): array
    {
        $data = [
            'id' => $note->id,
            'name' => $note->name,
            'datetime' => $note->datetime?->toIso8601String(),
            'parent_id' => $note->parent_id,
            'parent_name' => $note->parent?->name,
            'children_count' => $note->children_count ?? 0,
            'stakeholders' => $note->stakeholders->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'company' => $s->company,
            ])->toArray(),
            'scopes' => $note->scopes->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'slug' => $s->slug,
                'color' => $s->color,
            ])->toArray(),
            'attachments_count' => $note->getMedia('attachments')->count(),
            'created_at' => $note->created_at->toIso8601String(),
            'updated_at' => $note->updated_at->toIso8601String(),
        ];

        if ($includeContent) {
            $data['short_summary'] = $note->short_summary;
            $data['long_summary'] = $note->long_summary;
            $data['notes'] = $note->notes;
            $data['transcription'] = $note->transcription;
            $data['children'] = $note->children?->map(fn ($child) => [
                'id' => $child->id,
                'name' => $child->name,
                'datetime' => $child->datetime?->toIso8601String(),
            ])->toArray() ?? [];
            $data['attachments'] = $note->getMedia('attachments')->map(fn ($media) => [
                'id' => $media->id,
                'name' => $media->file_name,
                'size' => $media->size,
                'mime_type' => $media->mime_type,
                'url' => $media->getUrl(),
            ])->toArray();
        }

        return $data;
    }

    private function buildTreeNode(Note $note, int $depth = 0): array
    {
        $node = [
            'id' => $note->id,
            'name' => $note->name,
            'datetime' => $note->datetime?->toIso8601String(),
            'depth' => $depth,
            'children_count' => $note->children_count ?? 0,
            'scopes' => $note->scopes->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'color' => $s->color,
            ])->toArray(),
        ];

        // Recursively build children (limit depth to avoid infinite loops)
        if ($depth < 5 && $note->children_count > 0) {
            $children = $note->children()
                ->with(['scopes'])
                ->withCount('children')
                ->orderBy('sort_order')
                ->orderBy('datetime', 'desc')
                ->get();

            $node['children'] = $children->map(fn ($child) => $this->buildTreeNode($child, $depth + 1))->toArray();
        } else {
            $node['children'] = [];
        }

        return $node;
    }

    /**
     * Extract context snippets around search matches
     */
    private function extractContext(Note $note, string $query): array
    {
        $contexts = [];
        $fields = ['name', 'short_summary', 'long_summary', 'notes', 'transcription'];

        foreach ($fields as $field) {
            $content = strip_tags($note->$field ?? '');
            $pos = stripos($content, $query);

            if ($pos !== false) {
                $start = max(0, $pos - 50);
                $length = min(150, strlen($content) - $start);
                $snippet = substr($content, $start, $length);

                // Add ellipsis
                $prefix = $start > 0 ? '...' : '';
                $suffix = ($start + $length) < strlen($content) ? '...' : '';

                $contexts[$field] = $prefix.$snippet.$suffix;
            }
        }

        return $contexts;
    }

    /**
     * Read the text content of an attachment from a note
     * Supports PDF, Word, Excel, PowerPoint, and text files
     */
    public function readAttachmentContent(array $args): array
    {
        $noteId = $args['note_id'] ?? null;
        $attachmentId = $args['attachment_id'] ?? null;

        if (!$noteId) {
            return [
                'success' => false,
                'error_code' => 'VALIDATION_ERROR',
                'error' => 'note_id is required',
            ];
        }

        if (!$attachmentId) {
            return [
                'success' => false,
                'error_code' => 'VALIDATION_ERROR',
                'error' => 'attachment_id is required',
            ];
        }

        // Find the note
        try {
            $note = Note::findOrFail($noteId);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('note', $noteId);
        }

        // Find the attachment in this note's media
        $attachment = $note->getMedia('attachments')->firstWhere('id', $attachmentId);

        if (!$attachment) {
            // Check if attachment exists but belongs to another note
            $existsElsewhere = Media::find($attachmentId);
            if ($existsElsewhere) {
                return [
                    'success' => false,
                    'error_code' => 'ATTACHMENT_NOT_IN_NOTE',
                    'error' => "Attachment #{$attachmentId} exists but is not attached to note #{$noteId}",
                ];
            }

            return [
                'success' => false,
                'error_code' => 'ATTACHMENT_NOT_FOUND',
                'error' => "Attachment #{$attachmentId} not found",
            ];
        }

        // Check if file type is supported
        if (!$this->documentExtractor->isSupported($attachment->mime_type)) {
            return [
                'success' => false,
                'error_code' => DocumentExtractionService::ERROR_UNSUPPORTED_TYPE,
                'error' => "Unsupported file type: {$attachment->mime_type}",
                'note_id' => $noteId,
                'attachment' => [
                    'id' => $attachment->id,
                    'name' => $attachment->file_name,
                    'mime_type' => $attachment->mime_type,
                    'size' => $attachment->size,
                ],
                'supported_types' => $this->documentExtractor->getSupportedTypes(),
            ];
        }

        // Extract content
        $result = $this->documentExtractor->extractFromMedia($attachment);

        // Build response
        $response = [
            'note_id' => $noteId,
            'note_name' => $note->name,
            'attachment' => [
                'id' => $attachment->id,
                'name' => $attachment->file_name,
                'mime_type' => $attachment->mime_type,
                'size' => $attachment->size,
            ],
            'success' => $result['success'],
        ];

        if ($result['success']) {
            $response['text'] = $result['text'];
            $response['metadata'] = $result['metadata'] ?? [];
            $response['cached'] = $result['cached'] ?? false;

            if (isset($result['warning'])) {
                $response['warning'] = $result['warning'];
                $response['warning_code'] = $result['warning_code'] ?? null;
            }
        } else {
            $response['error_code'] = $result['error_code'];
            $response['error'] = $result['error'];
        }

        return $response;
    }
}
