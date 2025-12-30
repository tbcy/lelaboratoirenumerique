<?php

namespace App\Services\Mcp\Tools;

use App\Exceptions\Mcp\InvalidOperationException;
use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Http\Requests\Mcp\Post\CreatePostRequest;
use App\Http\Requests\Mcp\Post\UpdatePostRequest;
use App\Models\Post;
use App\Models\User;
use App\Services\Mcp\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class PostTools
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    public function create(array $args): array
    {
        $request = new CreatePostRequest;
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

        $validated = $validator->validated();

        // Get author (default to first admin user)
        $authorId = $validated['author_id'] ?? User::first()?->id;
        if (! $authorId) {
            return [
                'success' => false,
                'message' => 'No author available. Please create a user first.',
            ];
        }

        // Extract tag_ids before creating post
        $tagIds = $validated['tag_ids'] ?? [];
        unset($validated['tag_ids']);

        $post = Post::create([
            ...$validated,
            'author_id' => $authorId,
            'status' => $validated['status'] ?? 'draft',
        ]);

        // Attach tags
        if (! empty($tagIds)) {
            $post->tags()->sync($tagIds);
        }

        $this->auditLog->logCreate('post', $post->id, $post->toArray());

        return [
            'success' => true,
            'message' => 'Post created successfully',
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'status' => $post->status,
        ];
    }

    public function update(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'message' => 'Post ID is required',
            ];
        }

        try {
            $post = Post::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('post', $id);
        }

        $request = new UpdatePostRequest;
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

        $validated = $validator->validated();
        $oldData = $post->toArray();

        // Extract tag_ids before updating post
        $tagIds = $validated['tag_ids'] ?? null;
        unset($validated['tag_ids'], $validated['id']);

        // Update only provided fields
        $post->update($validated);

        // Sync tags if provided
        if ($tagIds !== null) {
            $post->tags()->sync($tagIds);
        }

        $this->auditLog->logUpdate('post', $post->id, $post->fresh()->toArray(), $oldData);

        return [
            'success' => true,
            'message' => 'Post updated successfully',
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'status' => $post->status,
        ];
    }

    public function delete(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'message' => 'Post ID is required',
            ];
        }

        try {
            $post = Post::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('post', $id);
        }

        $postData = $post->toArray();
        $post->delete();

        $this->auditLog->logDelete('post', $id, $postData);

        return [
            'success' => true,
            'message' => 'Post deleted successfully (soft delete)',
            'id' => $id,
        ];
    }

    public function publish(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'message' => 'Post ID is required',
            ];
        }

        try {
            $post = Post::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('post', $id);
        }

        if ($post->status === 'published') {
            throw InvalidOperationException::make('Post is already published', [
                'post_id' => $id,
                'current_status' => $post->status,
            ]);
        }

        $oldData = $post->toArray();

        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->auditLog->logUpdate('post', $post->id, $post->fresh()->toArray(), $oldData);

        return [
            'success' => true,
            'message' => 'Post published successfully',
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'status' => $post->status,
            'published_at' => $post->published_at->toIso8601String(),
        ];
    }

    public function unpublish(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'message' => 'Post ID is required',
            ];
        }

        try {
            $post = Post::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('post', $id);
        }

        if ($post->status === 'draft') {
            throw InvalidOperationException::make('Post is already a draft', [
                'post_id' => $id,
                'current_status' => $post->status,
            ]);
        }

        $oldData = $post->toArray();

        $post->update([
            'status' => 'draft',
        ]);

        $this->auditLog->logUpdate('post', $post->id, $post->fresh()->toArray(), $oldData);

        return [
            'success' => true,
            'message' => 'Post unpublished successfully',
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'status' => $post->status,
        ];
    }
}
