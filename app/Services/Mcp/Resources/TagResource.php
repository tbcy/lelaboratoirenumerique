<?php

namespace App\Services\Mcp\Resources;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\Tag;
use App\Services\Mcp\PaginationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TagResource
{
    public function __construct(
        private PaginationService $pagination
    ) {}

    public function listResources(): array
    {
        return [
            [
                'uri' => 'labo://tags',
                'name' => 'Blog Tags',
                'description' => 'List and read blog tags',
                'mimeType' => 'application/json',
            ],
        ];
    }

    public function list(array $filters = []): array
    {
        $query = Tag::query()
            ->withCount('posts')
            ->orderBy('name');

        $result = $this->pagination->paginate($query, $filters);

        return [
            'tags' => collect($result['data'])->map(fn ($tag) => $this->transformTag($tag))->toArray(),
            'pagination' => $result['pagination'],
        ];
    }

    public function get(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'error' => 'Tag ID is required',
            ];
        }

        try {
            $tag = Tag::withCount('posts')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('tag', $id);
        }

        return $this->transformTag($tag);
    }

    public function read(): array
    {
        return $this->list();
    }

    public function readSingle(int $id): array
    {
        return $this->get(['id' => $id]);
    }

    private function transformTag(Tag $tag): array
    {
        return [
            'id' => $tag->id,
            'name' => $tag->name,
            'slug' => $tag->slug,
            'posts_count' => $tag->posts_count ?? 0,
            'created_at' => $tag->created_at->toIso8601String(),
            'updated_at' => $tag->updated_at->toIso8601String(),
        ];
    }
}
