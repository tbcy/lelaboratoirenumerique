<?php

namespace App\Services\Mcp\Resources;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\Stakeholder;
use App\Services\Mcp\PaginationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StakeholderResource
{
    public function __construct(
        private PaginationService $pagination
    ) {}

    public function listResources(): array
    {
        return [
            [
                'uri' => 'labo://stakeholders',
                'name' => 'Stakeholders',
                'description' => 'List and read stakeholders (participants)',
                'mimeType' => 'application/json',
            ],
        ];
    }

    public function list(array $filters = []): array
    {
        $query = Stakeholder::query()
            ->withCount(['notes', 'tasks'])
            ->orderBy('name');

        // Filter by search
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('company', 'LIKE', "%{$search}%");
            });
        }

        // Filter by active status
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        $result = $this->pagination->paginate($query, $filters);

        return [
            'stakeholders' => collect($result['data'])->map(fn ($stakeholder) => $this->transformStakeholder($stakeholder))->toArray(),
            'pagination' => $result['pagination'],
        ];
    }

    public function get(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'error' => 'Stakeholder ID is required',
            ];
        }

        try {
            $stakeholder = Stakeholder::withCount(['notes', 'tasks'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('stakeholder', $id);
        }

        return $this->transformStakeholder($stakeholder);
    }

    public function read(): array
    {
        return $this->list();
    }

    public function readSingle(int $id): array
    {
        return $this->get(['id' => $id]);
    }

    private function transformStakeholder(Stakeholder $stakeholder): array
    {
        return [
            'id' => $stakeholder->id,
            'name' => $stakeholder->name,
            'email' => $stakeholder->email,
            'phone' => $stakeholder->phone,
            'company' => $stakeholder->company,
            'role' => $stakeholder->role,
            'notes' => $stakeholder->notes,
            'is_active' => $stakeholder->is_active,
            'notes_count' => $stakeholder->notes_count ?? 0,
            'tasks_count' => $stakeholder->tasks_count ?? 0,
            'created_at' => $stakeholder->created_at->toIso8601String(),
            'updated_at' => $stakeholder->updated_at->toIso8601String(),
        ];
    }
}
