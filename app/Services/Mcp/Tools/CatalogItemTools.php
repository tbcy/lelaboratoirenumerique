<?php

namespace App\Services\Mcp\Tools;

use App\Http\Requests\Mcp\CatalogItem\CreateCatalogItemRequest;
use App\Http\Requests\Mcp\CatalogItem\UpdateCatalogItemRequest;
use App\Models\CatalogItem;
use App\Services\Mcp\AuditLogService;
use Illuminate\Support\Facades\Validator;

class CatalogItemTools
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    public function create(array $args): array
    {
        // Validate input
        $request = new CreateCatalogItemRequest();
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

            $item = CatalogItem::create([
                'catalog_category_id' => $validated['catalog_category_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'unit_price' => $validated['unit_price'],
                'unit' => $validated['unit'] ?? 'unit',
                'vat_rate' => $validated['vat_rate'] ?? 20,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $this->auditLog->log(
                'create',
                'catalog_item',
                $item->id,
                $item->toArray()
            );

            return [
                'success' => true,
                'message' => 'Catalog item created successfully',
                'id' => $item->id,
                'name' => $item->name,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create catalog item',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function update(array $args): array
    {
        // Validate input
        $request = new UpdateCatalogItemRequest();
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
            $item = CatalogItem::findOrFail($validated['id']);
            $oldValues = $item->toArray();

            $fillable = [
                'catalog_category_id', 'name', 'description',
                'unit_price', 'unit', 'vat_rate', 'default_quantity', 'is_active'
            ];

            $updates = array_intersect_key($validated, array_flip($fillable));
            $item->update($updates);

            $this->auditLog->log(
                'update',
                'catalog_item',
                $item->id,
                [
                    'old' => array_intersect_key($oldValues, $updates),
                    'new' => $updates,
                ]
            );

            return [
                'success' => true,
                'message' => 'Catalog item updated successfully',
                'id' => $item->id,
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Catalog item not found',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update catalog item',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function delete(int $id): array
    {
        try {
            $item = CatalogItem::findOrFail($id);
            $itemName = $item->name;
            $item->delete();

            $this->auditLog->log(
                'delete',
                'catalog_item',
                $id,
                ['name' => $itemName]
            );

            return [
                'success' => true,
                'message' => 'Catalog item deleted successfully',
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Catalog item not found',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete catalog item',
                'error' => $e->getMessage(),
            ];
        }
    }
}
