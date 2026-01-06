<?php

namespace App\Services\Mcp\Resources;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\Note;
use App\Services\Mcp\PaginationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NoteResource
{
    public function __construct(
        private PaginationService $pagination
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
     * Full-text search across all note content fields
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

        $query = Note::query()
            ->with(['stakeholders', 'scopes', 'parent'])
            ->withCount('children');

        // Search in all content fields
        $query->where(function ($q) use ($queryStr) {
            $searchTerm = '%'.strtolower($queryStr).'%';

            $q->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                ->orWhereRaw('LOWER(short_summary) LIKE ?', [$searchTerm])
                ->orWhereRaw('LOWER(long_summary) LIKE ?', [$searchTerm])
                ->orWhereRaw('LOWER(notes) LIKE ?', [$searchTerm])
                ->orWhereRaw('LOWER(transcription) LIKE ?', [$searchTerm]);
        });

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
            $query->where('datetime', '>=', $args['date_from']);
        }
        if (! empty($args['date_to'])) {
            $query->where('datetime', '<=', $args['date_to']);
        }

        $notes = $query->orderBy('datetime', 'desc')->limit(50)->get();

        return [
            'query' => $queryStr,
            'results_count' => $notes->count(),
            'notes' => $notes->map(fn ($note) => [
                ...$this->transformNote($note),
                'match_context' => $this->extractContext($note, $queryStr),
            ])->toArray(),
        ];
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
}
