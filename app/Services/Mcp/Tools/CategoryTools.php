<?php

namespace App\Services\Mcp\Tools;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\Category;
use App\Services\Mcp\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryTools
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    public function create(array $args): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
        ];

        $validator = Validator::make($args, $rules);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        $validated = $validator->validated();

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category = Category::create($validated);

        $this->auditLog->logCreate('category', $category->id, $category->toArray());

        return [
            'success' => true,
            'message' => 'Category created successfully',
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
        ];
    }

    public function update(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'message' => 'Category ID is required',
            ];
        }

        try {
            $category = Category::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('category', $id);
        }

        $rules = [
            'id' => 'required|integer',
            'name' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,'.$id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
        ];

        $validator = Validator::make($args, $rules);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        $validated = $validator->validated();
        $oldData = $category->toArray();

        unset($validated['id']);

        // Update only provided fields
        $category->update($validated);

        $this->auditLog->logUpdate('category', $category->id, $category->fresh()->toArray(), $oldData);

        return [
            'success' => true,
            'message' => 'Category updated successfully',
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
        ];
    }

    public function delete(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'message' => 'Category ID is required',
            ];
        }

        try {
            $category = Category::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('category', $id);
        }

        // Check if category has posts
        $postsCount = $category->posts()->count();
        if ($postsCount > 0) {
            return [
                'success' => false,
                'message' => "Cannot delete category: {$postsCount} posts are using this category. Reassign or delete them first.",
            ];
        }

        $categoryData = $category->toArray();
        $category->delete();

        $this->auditLog->logDelete('category', $id, $categoryData);

        return [
            'success' => true,
            'message' => 'Category deleted successfully',
            'id' => $id,
        ];
    }
}
