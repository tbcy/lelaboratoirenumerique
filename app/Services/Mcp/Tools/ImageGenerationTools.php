<?php

namespace App\Services\Mcp\Tools;

use App\Models\GeneratedImage;
use App\Models\SocialPost;
use App\Services\DalleImageService;
use App\Services\Mcp\AuditLogService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImageGenerationTools
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    /**
     * Generate an image from text content using GPT Image 1.5
     *
     * @param array $args Arguments with 'content' and optional 'social_post_id'
     * @return array
     */
    public function generate(array $args): array
    {
        $validator = Validator::make($args, [
            'content' => 'required|string|min:10|max:5000',
            'social_post_id' => 'nullable|integer|exists:social_posts,id',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        $validated = $validator->validated();

        $service = new DalleImageService();

        if (! $service->isConfigured()) {
            return [
                'success' => false,
                'message' => 'OpenAI API is not configured',
                'error' => 'Please configure your OpenAI API key in Settings > Integrations',
            ];
        }

        $socialPost = null;
        if (! empty($validated['social_post_id'])) {
            $socialPost = SocialPost::find($validated['social_post_id']);
        }

        $result = $service->generate($validated['content'], $socialPost);

        if (! $result['success']) {
            return [
                'success' => false,
                'message' => 'Image generation failed',
                'error' => $result['error'],
            ];
        }

        $image = $result['image'];

        $this->auditLog->log(
            'generate_image',
            'generated_image',
            $image->id,
            [
                'content_preview' => substr($validated['content'], 0, 100),
                'social_post_id' => $socialPost?->id,
            ]
        );

        return [
            'success' => true,
            'message' => 'Image generated successfully',
            'generated_image' => [
                'id' => $image->id,
                'file_name' => $image->file_name,
                'file_path' => $image->file_path,
                'file_size' => $image->file_size,
                'mime_type' => $image->mime_type,
                'created_at' => $image->created_at->toIso8601String(),
            ],
        ];
    }

    /**
     * Generate an image and attach it to a social post
     *
     * @param array $args Arguments with 'social_post_id'
     * @return array
     */
    public function generateForPost(array $args): array
    {
        $validator = Validator::make($args, [
            'social_post_id' => 'required|integer|exists:social_posts,id',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        $socialPost = SocialPost::find($args['social_post_id']);

        if (empty(trim($socialPost->content))) {
            return [
                'success' => false,
                'message' => 'Social post has no content to generate an image from',
            ];
        }

        $service = new DalleImageService();

        if (! $service->isConfigured()) {
            return [
                'success' => false,
                'message' => 'OpenAI API is not configured',
                'error' => 'Please configure your OpenAI API key in Settings > Integrations',
            ];
        }

        $result = $service->generateForSocialPost($socialPost);

        if (! $result['success']) {
            return [
                'success' => false,
                'message' => 'Image generation failed',
                'error' => $result['error'],
            ];
        }

        $image = $result['image'];

        $this->auditLog->log(
            'generate_image_for_post',
            'generated_image',
            $image->id,
            [
                'social_post_id' => $socialPost->id,
            ]
        );

        return [
            'success' => true,
            'message' => 'Image generated and attached to social post',
            'generated_image' => [
                'id' => $image->id,
                'file_name' => $image->file_name,
                'file_path' => $image->file_path,
                'file_size' => $image->file_size,
                'mime_type' => $image->mime_type,
                'created_at' => $image->created_at->toIso8601String(),
            ],
            'social_post' => [
                'id' => $socialPost->id,
                'images_count' => count($socialPost->fresh()->images ?? []),
            ],
        ];
    }

    /**
     * List generated images
     *
     * @param array $args Optional filters
     * @return array
     */
    public function list(array $args): array
    {
        $query = GeneratedImage::query()->orderBy('created_at', 'desc');

        if (! empty($args['social_post_id'])) {
            $query->where('social_post_id', $args['social_post_id']);
        }

        if (isset($args['has_social_post'])) {
            if ($args['has_social_post']) {
                $query->whereNotNull('social_post_id');
            } else {
                $query->whereNull('social_post_id');
            }
        }

        $limit = min($args['limit'] ?? 20, 100);
        $images = $query->limit($limit)->get();

        return [
            'success' => true,
            'count' => $images->count(),
            'images' => $images->map(fn ($image) => [
                'id' => $image->id,
                'file_name' => $image->file_name,
                'file_path' => $image->file_path,
                'file_size' => $image->file_size,
                'mime_type' => $image->mime_type,
                'social_post_id' => $image->social_post_id,
                'content_preview' => substr($image->content_source ?? '', 0, 100),
                'created_at' => $image->created_at->toIso8601String(),
            ])->toArray(),
        ];
    }

    /**
     * Delete a generated image
     *
     * @param array $args Arguments with 'id'
     * @return array
     */
    public function delete(array $args): array
    {
        $validator = Validator::make($args, [
            'id' => 'required|integer|exists:generated_images,id',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        $image = GeneratedImage::find($args['id']);
        $imageId = $image->id;
        $fileName = $image->file_name;

        // Delete file from storage
        if (Storage::disk('local')->exists($image->file_path)) {
            Storage::disk('local')->delete($image->file_path);
        }

        $image->delete();

        $this->auditLog->log(
            'delete',
            'generated_image',
            $imageId,
            ['file_name' => $fileName]
        );

        return [
            'success' => true,
            'message' => 'Generated image deleted successfully',
            'id' => $imageId,
        ];
    }
}
