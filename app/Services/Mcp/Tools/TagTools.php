<?php

namespace App\Services\Mcp\Tools;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\Tag;
use App\Services\Mcp\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TagTools
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    public function create(array $args): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:tags,slug',
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

        $tag = Tag::create($validated);

        $this->auditLog->logCreate('tag', $tag->id, $tag->toArray());

        return [
            'success' => true,
            'message' => 'Tag created successfully',
            'id' => $tag->id,
            'name' => $tag->name,
            'slug' => $tag->slug,
        ];
    }

    public function update(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'message' => 'Tag ID is required',
            ];
        }

        try {
            $tag = Tag::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('tag', $id);
        }

        $rules = [
            'id' => 'required|integer',
            'name' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:tags,slug,'.$id,
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
        $oldData = $tag->toArray();

        unset($validated['id']);

        // Update only provided fields
        $tag->update($validated);

        $this->auditLog->logUpdate('tag', $tag->id, $tag->fresh()->toArray(), $oldData);

        return [
            'success' => true,
            'message' => 'Tag updated successfully',
            'id' => $tag->id,
            'name' => $tag->name,
            'slug' => $tag->slug,
        ];
    }

    public function delete(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'message' => 'Tag ID is required',
            ];
        }

        try {
            $tag = Tag::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('tag', $id);
        }

        $tagData = $tag->toArray();

        // Detach tag from all posts before deleting
        $tag->posts()->detach();
        $tag->delete();

        $this->auditLog->logDelete('tag', $id, $tagData);

        return [
            'success' => true,
            'message' => 'Tag deleted successfully',
            'id' => $id,
        ];
    }
}
