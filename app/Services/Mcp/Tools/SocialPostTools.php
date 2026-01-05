<?php

namespace App\Services\Mcp\Tools;

use App\Http\Requests\Mcp\SocialPost\CreateSocialPostRequest;
use App\Http\Requests\Mcp\SocialPost\UpdateSocialPostRequest;
use App\Http\Requests\Mcp\SocialPost\ApproveSocialPostRequest;
use App\Http\Requests\Mcp\SocialPost\PublishSocialPostRequest;
use App\Models\SocialPost;
use App\Models\SocialConnection;
use App\Services\Mcp\AuditLogService;
use App\Services\Social\LinkedInConnector;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SocialPostTools
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    /**
     * Create a new social post
     *
     * @param array $args Social post data
     * @return array
     */
    public function create(array $args): array
    {
        // Validate input
        $request = new CreateSocialPostRequest();
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

            // Handle media_ids - copy from Media Library to social-posts
            $images = $validated['images'] ?? null;
            if (!empty($args['media_ids'])) {
                $images = $this->copyMediaToSocialPosts($args['media_ids']);
            }

            $post = SocialPost::create([
                'content' => $validated['content'],
                'images' => $images,
                'connection_ids' => $validated['connection_ids'] ?? [],
                'status' => $validated['status'] ?? 'draft',
                'scheduled_at' => $validated['scheduled_at'] ?? null,
            ]);

            $this->auditLog->log(
                'create',
                'social_post',
                $post->id,
                $post->toArray()
            );

            return [
                'success' => true,
                'message' => 'Social post created successfully',
                'id' => $post->id,
                'status' => $post->status,
                'images_count' => is_array($images) ? count($images) : 0,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create social post',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update an existing social post
     *
     * @param array $args Social post data with id
     * @return array
     */
    public function update(array $args): array
    {
        // Validate input
        $request = new UpdateSocialPostRequest();
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
            $post = SocialPost::findOrFail($validated['id']);
            $oldValues = $post->toArray();

            $fillable = ['content', 'images', 'connection_ids', 'status', 'scheduled_at'];
            $updates = array_intersect_key($validated, array_flip($fillable));

            $post->update($updates);

            $this->auditLog->log(
                'update',
                'social_post',
                $post->id,
                [
                    'old' => array_intersect_key($oldValues, $updates),
                    'new' => $updates,
                ]
            );

            return [
                'success' => true,
                'message' => 'Social post updated successfully',
                'id' => $post->id,
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Social post not found',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update social post',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Approve a social post
     *
     * @param array $args Arguments with 'id' key
     * @return array
     */
    public function approve(array $args): array
    {
        // Validate input
        $request = new ApproveSocialPostRequest();
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
            $post = SocialPost::findOrFail($validated['id']);

            $oldStatus = $post->status;
            $post->approve();

            $this->auditLog->log(
                'approve',
                'social_post',
                $post->id,
                [
                    'old_status' => $oldStatus,
                    'new_status' => 'approved',
                ]
            );

            return [
                'success' => true,
                'message' => 'Social post approved successfully',
                'id' => $post->id,
                'status' => $post->status,
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Social post not found',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to approve social post',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Publish a social post to selected platforms
     *
     * @param array $args Arguments with 'id' key
     * @return array
     */
    public function publish(array $args): array
    {
        // Validate input
        $request = new PublishSocialPostRequest();
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
            $post = SocialPost::findOrFail($validated['id']);

            if (!in_array($post->status, ['approved', 'scheduled'])) {
                return [
                    'success' => false,
                    'message' => 'Only approved or scheduled posts can be published',
                ];
            }

            if (empty($post->connection_ids)) {
                return [
                    'success' => false,
                    'message' => 'No social connections selected for this post',
                ];
            }

            $results = [];
            $hasErrors = false;

            foreach ($post->connection_ids as $connectionId) {
                try {
                    $connection = SocialConnection::findOrFail($connectionId);

                    if (!$connection->is_active) {
                        $results[] = [
                            'platform' => $connection->platform,
                            'success' => false,
                            'error' => 'Connection is not active',
                        ];
                        $hasErrors = true;
                        continue;
                    }

                    // Publish based on platform
                    if ($connection->platform === 'linkedin') {
                        $connector = new LinkedInConnector($connection);
                        $result = $connector->publishPost($post);
                        $results[] = array_merge(['platform' => 'linkedin'], $result);

                        if (!$result['success']) {
                            $hasErrors = true;
                        }
                    } else {
                        $results[] = [
                            'platform' => $connection->platform,
                            'success' => false,
                            'error' => 'Platform not yet implemented',
                        ];
                        $hasErrors = true;
                    }
                } catch (\Exception $e) {
                    $results[] = [
                        'platform' => $connection->platform ?? 'unknown',
                        'success' => false,
                        'error' => $e->getMessage(),
                    ];
                    $hasErrors = true;
                }
            }

            // Update post status
            if ($hasErrors) {
                $post->markAsFailed(json_encode($results));
            } else {
                $post->markAsPublished();
            }

            $this->auditLog->log(
                'publish',
                'social_post',
                $post->id,
                [
                    'results' => $results,
                    'final_status' => $post->status,
                ]
            );

            return [
                'success' => !$hasErrors,
                'message' => $hasErrors ? 'Post published with errors' : 'Post published successfully',
                'id' => $post->id,
                'status' => $post->status,
                'results' => $results,
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Social post not found',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to publish social post',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Copy media files from Media Library to social-posts directory
     *
     * @param array $mediaIds Media IDs to copy
     * @return array Array of copied file paths
     */
    private function copyMediaToSocialPosts(array $mediaIds): array
    {
        $copiedPaths = [];

        foreach ($mediaIds as $mediaId) {
            $media = Media::find($mediaId);
            if (!$media) {
                continue;
            }

            // Source path in media library
            $sourcePath = 'media/' . $media->id . '/' . $media->file_name;

            if (!Storage::disk('local')->exists($sourcePath)) {
                continue;
            }

            // Generate unique filename for social-posts
            $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
            $newFilename = Str::uuid() . '.' . $extension;
            $destPath = 'social-posts/' . $newFilename;

            // Copy file
            Storage::disk('local')->copy($sourcePath, $destPath);

            $copiedPaths[] = $destPath;
        }

        return $copiedPaths;
    }

    /**
     * Delete a social post
     *
     * @param array $args Arguments with 'id' key
     * @return array
     */
    public function delete(array $args): array
    {
        $validator = Validator::make($args, [
            'id' => 'required|integer|exists:social_posts,id',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        try {
            $post = SocialPost::findOrFail($args['id']);

            // Cannot delete published posts
            if ($post->status === 'published') {
                return [
                    'success' => false,
                    'message' => 'Cannot delete a published post',
                ];
            }

            $postData = $post->toArray();

            // Clean up any images stored for this post
            if (!empty($post->images)) {
                foreach ($post->images as $imagePath) {
                    if (Storage::disk('local')->exists($imagePath)) {
                        Storage::disk('local')->delete($imagePath);
                    }
                }
            }

            $post->delete();

            $this->auditLog->log(
                'delete',
                'social_post',
                $args['id'],
                $postData
            );

            return [
                'success' => true,
                'message' => 'Social post deleted successfully',
                'id' => $args['id'],
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Social post not found',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete social post',
                'error' => $e->getMessage(),
            ];
        }
    }
}
