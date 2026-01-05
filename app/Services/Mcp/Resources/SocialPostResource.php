<?php

namespace App\Services\Mcp\Resources;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\SocialPost;
use App\Services\Mcp\ErrorHandlingService;
use App\Services\Mcp\PaginationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SocialPostResource
{
    public function __construct(
        private PaginationService $pagination,
        private ErrorHandlingService $errorHandler
    ) {}

    /**
     * List social posts with optional filters and pagination
     *
     * @param  array  $filters  Optional filters: status, page, per_page
     */
    public function list(array $filters = []): array
    {
        $query = SocialPost::query();

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->orderBy('created_at', 'desc');

        // Use pagination service
        $result = $this->pagination->paginate($query, $filters);

        return [
            'posts' => collect($result['data'])->map(function ($post) {
                return [
                    'id' => $post->id,
                    'content' => $post->content,
                    'images' => $post->images,
                    'connection_ids' => $post->connection_ids,
                    'status' => $post->status,
                    'scheduled_at' => $post->scheduled_at,
                    'published_at' => $post->published_at,
                    'error_message' => $post->error_message,
                    'created_at' => $post->created_at,
                    'updated_at' => $post->updated_at,
                ];
            })->toArray(),
            'pagination' => $result['pagination'],
        ];
    }

    /**
     * Get a single social post
     *
     * @throws ResourceNotFoundException
     */
    public function get(int $id): array
    {
        try {
            $post = SocialPost::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('social_post', $id);
        }

        return [
            'id' => $post->id,
            'content' => $post->content,
            'images' => $post->images,
            'connection_ids' => $post->connection_ids,
            'connections' => $post->connections()->map(fn ($conn) => [
                'id' => $conn->id,
                'name' => $conn->name,
                'platform' => $conn->platform,
                'is_active' => $conn->is_active,
            ])->toArray(),
            'status' => $post->status,
            'scheduled_at' => $post->scheduled_at,
            'published_at' => $post->published_at,
            'error_message' => $post->error_message,
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
        ];
    }
}
