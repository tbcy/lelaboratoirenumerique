<?php

namespace App\Services\Mcp\Tools;

use App\Http\Requests\Mcp\CatalogCategory\CreateCatalogCategoryRequest;
use App\Http\Requests\Mcp\CatalogCategory\UpdateCatalogCategoryRequest;
use App\Models\CatalogCategory;
use App\Services\Mcp\AuditLogService;
use Illuminate\Support\Facades\Validator;

class CatalogCategoryTools
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    /**
     * Create a new catalog category
     *
     * @param array $args Category data
     * @return array
     */
    public function create(array $args): array
    {
        // Validate input
        $request = new CreateCatalogCategoryRequest();
        $validator = Validator::make($args, $request->rules(), $request->messages());

        if (method_exists($request, 'withValidator')) {
            $request->withValidator($validator);
        }

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        try {
            $validated = $validator->validated();

            $category = CatalogCategory::create([
                'name' => $validated['name'],
                'color' => $validated['color'] ?? null,
                'sort_order' => $validated['sort_order'] ?? null,
            ]);

            $this->auditLog->log(
                'create',
                'catalog_category',
                $category->id,
                $category->toArray()
            );

            return [
                'success' => true,
                'message' => 'Catalog category created successfully',
                'id' => $category->id,
                'name' => $category->name,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create catalog category',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update an existing catalog category
     *
     * @param array $args Category data with id
     * @return array
     */
    public function update(array $args): array
    {
        // Validate input
        $request = new UpdateCatalogCategoryRequest();
        $validator = Validator::make($args, $request->rules(), $request->messages());

        if (method_exists($request, 'withValidator')) {
            $request->withValidator($validator);
        }

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        try {
            $validated = $validator->validated();
            $category = CatalogCategory::findOrFail($validated['id']);
            $oldValues = $category->toArray();

            $fillable = ['name', 'color', 'sort_order'];
            $updates = array_intersect_key($validated, array_flip($fillable));

            $category->update($updates);

            $this->auditLog->log(
                'update',
                'catalog_category',
                $category->id,
                [
                    'old' => array_intersect_key($oldValues, $updates),
                    'new' => $updates,
                ]
            );

            return [
                'success' => true,
                'message' => 'Catalog category updated successfully',
                'id' => $category->id,
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Catalog category not found',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update catalog category',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a catalog category (soft delete)
     *
     * @param int $id Category ID
     * @return array
     */
    public function delete(int $id): array
    {
        try {
            $category = CatalogCategory::findOrFail($id);

            // Check if category has items
            if ($category->items()->count() > 0) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete category with items',
                ];
            }

            $categoryName = $category->name;
            $category->delete();

            $this->auditLog->log(
                'delete',
                'catalog_category',
                $id,
                ['name' => $categoryName]
            );

            return [
                'success' => true,
                'message' => 'Catalog category deleted successfully',
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Catalog category not found',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete catalog category',
                'error' => $e->getMessage(),
            ];
        }
    }
}
