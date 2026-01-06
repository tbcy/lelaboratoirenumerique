<?php

namespace App\Services\Mcp\Resources;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\NoteScope;
use App\Services\Mcp\PaginationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NoteScopeResource
{
    public function __construct(
        private PaginationService $pagination
    ) {}

    public function listResources(): array
    {
        return [
            [
                'uri' => 'labo://note-scopes',
                'name' => 'Note Scopes',
                'description' => 'List and read note scopes (tags)',
                'mimeType' => 'application/json',
            ],
        ];
    }

    public function list(array $filters = []): array
    {
        $query = NoteScope::query()
            ->withCount('notes')
            ->orderBy('name');

        $result = $this->pagination->paginate($query, $filters);

        return [
            'note_scopes' => collect($result['data'])->map(fn ($scope) => $this->transformScope($scope))->toArray(),
            'pagination' => $result['pagination'],
        ];
    }

    public function get(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'error' => 'Note Scope ID is required',
            ];
        }

        try {
            $scope = NoteScope::withCount('notes')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('note_scope', $id);
        }

        return $this->transformScope($scope);
    }

    public function read(): array
    {
        return $this->list();
    }

    public function readSingle(int $id): array
    {
        return $this->get(['id' => $id]);
    }

    private function transformScope(NoteScope $scope): array
    {
        return [
            'id' => $scope->id,
            'name' => $scope->name,
            'slug' => $scope->slug,
            'color' => $scope->color,
            'notes_count' => $scope->notes_count ?? 0,
            'created_at' => $scope->created_at->toIso8601String(),
            'updated_at' => $scope->updated_at->toIso8601String(),
        ];
    }
}
