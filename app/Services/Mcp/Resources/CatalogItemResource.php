<?php

namespace App\Services\Mcp\Resources;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\CatalogItem;
use App\Services\Mcp\ErrorHandlingService;
use App\Services\Mcp\PaginationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CatalogItemResource
{
    public function __construct(
        private PaginationService $pagination,
        private ErrorHandlingService $errorHandler
    ) {}

    /**
     * List catalog items with optional filters and pagination
     *
     * @param  array  $filters  Optional filters: category_id, is_active, page, per_page
     */
    public function list(array $filters = []): array
    {
        $query = CatalogItem::with('category');

        // Filter by category_id
        if (isset($filters['category_id'])) {
            $query->where('catalog_category_id', $filters['category_id']);
        }

        // Filter by is_active
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        $query->orderBy('name');

        // Use pagination service
        $result = $this->pagination->paginate($query, $filters);

        return [
            'items' => collect($result['data'])->map(function ($item) {
                return [
                    'id' => $item->id,
                    'catalog_category_id' => $item->catalog_category_id,
                    'category_name' => $item->category?->name,
                    'name' => $item->name,
                    'description' => $item->description,
                    'unit_price' => (float) $item->unit_price,
                    'unit' => $item->unit,
                    'vat_rate' => (float) $item->vat_rate,
                    'default_quantity' => (float) $item->default_quantity,
                    'is_active' => $item->is_active,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            })->toArray(),
            'pagination' => $result['pagination'],
        ];
    }

    /**
     * Get a single catalog item
     *
     * @throws ResourceNotFoundException
     */
    public function get(int $id): array
    {
        try {
            $item = CatalogItem::with('category')->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('catalog_item', $id);
        }

        return [
            'id' => $item->id,
            'catalog_category_id' => $item->catalog_category_id,
            'category' => $item->category ? [
                'id' => $item->category->id,
                'name' => $item->category->name,
                'color' => $item->category->color,
            ] : null,
            'name' => $item->name,
            'description' => $item->description,
            'unit_price' => (float) $item->unit_price,
            'unit' => $item->unit,
            'vat_rate' => (float) $item->vat_rate,
            'default_quantity' => (float) $item->default_quantity,
            'is_active' => $item->is_active,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ];
    }
}
