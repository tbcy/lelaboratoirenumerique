<?php

namespace App\Services\Mcp\Resources;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\Category;
use App\Services\Mcp\PaginationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryResource
{
    public function __construct(
        private PaginationService $pagination
    ) {}

    public function listResources(): array
    {
        return [
            [
                'uri' => 'labo://categories',
                'name' => 'Blog Categories',
                'description' => 'List and read blog categories',
                'mimeType' => 'application/json',
            ],
        ];
    }

    public function list(array $filters = []): array
    {
        $query = Category::query()
            ->withCount('posts')
            ->orderBy('sort_order')
            ->orderBy('name');

        $result = $this->pagination->paginate($query, $filters);

        return [
            'categories' => collect($result['data'])->map(fn ($category) => $this->transformCategory($category))->toArray(),
            'pagination' => $result['pagination'],
        ];
    }

    public function get(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'error' => 'Category ID is required',
            ];
        }

        try {
            $category = Category::withCount('posts')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('category', $id);
        }

        return $this->transformCategory($category);
    }

    public function read(): array
    {
        return $this->list();
    }

    public function readSingle(int $id): array
    {
        return $this->get(['id' => $id]);
    }

    private function transformCategory(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'color' => $category->color,
            'sort_order' => $category->sort_order,
            'posts_count' => $category->posts_count ?? 0,
            'created_at' => $category->created_at->toIso8601String(),
            'updated_at' => $category->updated_at->toIso8601String(),
        ];
    }
}
