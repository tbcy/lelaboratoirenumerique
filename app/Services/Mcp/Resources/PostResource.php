<?php

namespace App\Services\Mcp\Resources;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\Post;
use App\Services\Mcp\PaginationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PostResource
{
    public function __construct(
        private PaginationService $pagination
    ) {}

    public function listResources(): array
    {
        return [
            [
                'uri' => 'labo://posts',
                'name' => 'Blog Posts',
                'description' => 'List and read blog posts',
                'mimeType' => 'application/json',
            ],
        ];
    }

    public function list(array $filters = []): array
    {
        $query = Post::query()->with(['category', 'author', 'tags']);

        // Filter by status
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by category
        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filter by featured
        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        // Search in title and content
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $query->orderBy('created_at', 'desc');

        $result = $this->pagination->paginate($query, $filters);

        return [
            'posts' => collect($result['data'])->map(fn ($post) => $this->transformPost($post))->toArray(),
            'pagination' => $result['pagination'],
        ];
    }

    public function get(array $args): array
    {
        $id = $args['id'] ?? null;

        if (! $id) {
            return [
                'success' => false,
                'error' => 'Post ID is required',
            ];
        }

        try {
            $post = Post::with(['category', 'author', 'tags', 'media'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('post', $id);
        }

        return $this->transformPost($post, true);
    }

    public function read(): array
    {
        return $this->list();
    }

    public function readSingle(int $id): array
    {
        return $this->get(['id' => $id]);
    }

    private function transformPost(Post $post, bool $full = false): array
    {
        $data = [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'status' => $post->status,
            'is_featured' => $post->is_featured,
            'reading_time' => $post->reading_time,
            'published_at' => $post->published_at?->toIso8601String(),
            'created_at' => $post->created_at->toIso8601String(),
            'updated_at' => $post->updated_at->toIso8601String(),
            'category' => $post->category ? [
                'id' => $post->category->id,
                'name' => $post->category->name,
                'slug' => $post->category->slug,
            ] : null,
            'author' => $post->author ? [
                'id' => $post->author->id,
                'name' => $post->author->name,
            ] : null,
            'tags' => $post->tags->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ])->toArray(),
        ];

        if ($full) {
            $data['content'] = $post->content;
            $data['meta_title'] = $post->meta_title;
            $data['meta_description'] = $post->meta_description;
            $data['featured_image_url'] = $post->featured_image_url;
        }

        return $data;
    }
}
