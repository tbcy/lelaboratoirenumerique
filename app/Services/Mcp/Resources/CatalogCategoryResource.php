<?php

namespace App\Services\Mcp\Resources;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\CatalogCategory;
use App\Services\Mcp\ErrorHandlingService;
use App\Services\Mcp\PaginationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CatalogCategoryResource
{
    public function __construct(
        private PaginationService $pagination,
        private ErrorHandlingService $errorHandler
    ) {}

    /**
     * List catalog categories with pagination
     *
     * @param  array  $filters  Optional filters: page, per_page
     */
    public function list(array $filters = []): array
    {
        $query = CatalogCategory::with('items')
            ->orderBy('sort_order')
            ->orderBy('name');

        // Use pagination service
        $result = $this->pagination->paginate($query, $filters);

        return [
            'categories' => collect($result['data'])->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'color' => $category->color,
                    'sort_order' => $category->sort_order,
                    'items_count' => $category->items->count(),
                    'created_at' => $category->created_at,
                    'updated_at' => $category->updated_at,
                ];
            })->toArray(),
            'pagination' => $result['pagination'],
        ];
    }

    /**
     * Get a single catalog category with items
     *
     * @throws ResourceNotFoundException
     */
    public function get(int $id): array
    {
        try {
            $category = CatalogCategory::with('items')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('catalog_category', $id);
        }

        return [
            'id' => $category->id,
            'name' => $category->name,
            'color' => $category->color,
            'sort_order' => $category->sort_order,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
            'items' => $category->items->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'unit_price' => (float) $item->unit_price,
                'unit' => $item->unit,
                'is_active' => $item->is_active,
            ])->toArray(),
        ];
    }
}
