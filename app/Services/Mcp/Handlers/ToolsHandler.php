<?php

namespace App\Services\Mcp\Handlers;

use App\Services\Mcp\ErrorHandlingService;
use App\Services\Mcp\Resources\CategoryResource;
use App\Services\Mcp\Resources\PostResource;
use App\Services\Mcp\Resources\TagResource;
use App\Services\Mcp\Tools\PostTools;

class ToolsHandler
{
    private array $toolDefinitions = [];

    private array $toolExecutors = [];

    public function __construct(
        private PostResource $postResource,
        private PostTools $postTools,
        private CategoryResource $categoryResource,
        private TagResource $tagResource,
        private ErrorHandlingService $errorHandler
    ) {
        $this->buildToolExecutors();
        $this->buildToolDefinitions();
    }

    private function buildToolExecutors(): void
    {
        $this->toolExecutors = [
            // Post Tools
            'list_posts' => [$this->postResource, 'list'],
            'get_post' => [$this->postResource, 'get'],
            'create_post' => [$this->postTools, 'create'],
            'update_post' => [$this->postTools, 'update'],
            'delete_post' => [$this->postTools, 'delete'],
            'publish_post' => [$this->postTools, 'publish'],
            'unpublish_post' => [$this->postTools, 'unpublish'],

            // Category Tools
            'list_categories' => [$this->categoryResource, 'list'],
            'get_category' => [$this->categoryResource, 'get'],

            // Tag Tools
            'list_tags' => [$this->tagResource, 'list'],
            'get_tag' => [$this->tagResource, 'get'],
        ];
    }

    private function buildToolDefinitions(): void
    {
        // Post Tools
        $this->toolDefinitions[] = [
            'name' => 'list_posts',
            'description' => 'List all blog posts with optional filters. Returns paginated results.',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'status' => [
                        'type' => 'string',
                        'enum' => ['draft', 'published'],
                        'description' => 'Filter by status',
                    ],
                    'category_id' => [
                        'type' => 'integer',
                        'description' => 'Filter by category ID',
                    ],
                    'is_featured' => [
                        'type' => 'boolean',
                        'description' => 'Filter featured posts only',
                    ],
                    'search' => [
                        'type' => 'string',
                        'description' => 'Search in title and content',
                    ],
                    'page' => [
                        'type' => 'integer',
                        'description' => 'Page number (default: 1)',
                    ],
                    'per_page' => [
                        'type' => 'integer',
                        'description' => 'Items per page (default: 50, max: 100)',
                    ],
                ],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_post',
            'description' => 'Get a single blog post by ID with all its details',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'integer',
                        'description' => 'Post ID',
                    ],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'create_post',
            'description' => 'Create a new blog post. The post will be created as a draft by default.',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'title' => [
                        'type' => 'string',
                        'description' => 'Post title (required)',
                    ],
                    'content' => [
                        'type' => 'string',
                        'description' => 'Post content in HTML or Markdown (required)',
                    ],
                    'excerpt' => [
                        'type' => 'string',
                        'description' => 'Short excerpt/summary of the post',
                    ],
                    'slug' => [
                        'type' => 'string',
                        'description' => 'URL slug (auto-generated from title if not provided)',
                    ],
                    'category_id' => [
                        'type' => 'integer',
                        'description' => 'Category ID',
                    ],
                    'tag_ids' => [
                        'type' => 'array',
                        'items' => ['type' => 'integer'],
                        'description' => 'Array of tag IDs to attach',
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['draft', 'published'],
                        'description' => 'Post status (default: draft)',
                    ],
                    'is_featured' => [
                        'type' => 'boolean',
                        'description' => 'Mark as featured post',
                    ],
                    'meta_title' => [
                        'type' => 'string',
                        'description' => 'SEO meta title',
                    ],
                    'meta_description' => [
                        'type' => 'string',
                        'description' => 'SEO meta description',
                    ],
                ],
                'required' => ['title', 'content'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'update_post',
            'description' => 'Update an existing blog post. Only provided fields will be updated.',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'integer',
                        'description' => 'Post ID (required)',
                    ],
                    'title' => [
                        'type' => 'string',
                        'description' => 'Post title',
                    ],
                    'content' => [
                        'type' => 'string',
                        'description' => 'Post content',
                    ],
                    'excerpt' => [
                        'type' => 'string',
                        'description' => 'Short excerpt/summary',
                    ],
                    'slug' => [
                        'type' => 'string',
                        'description' => 'URL slug',
                    ],
                    'category_id' => [
                        'type' => 'integer',
                        'description' => 'Category ID',
                    ],
                    'tag_ids' => [
                        'type' => 'array',
                        'items' => ['type' => 'integer'],
                        'description' => 'Array of tag IDs (replaces existing tags)',
                    ],
                    'status' => [
                        'type' => 'string',
                        'enum' => ['draft', 'published'],
                        'description' => 'Post status',
                    ],
                    'is_featured' => [
                        'type' => 'boolean',
                        'description' => 'Mark as featured post',
                    ],
                    'meta_title' => [
                        'type' => 'string',
                        'description' => 'SEO meta title',
                    ],
                    'meta_description' => [
                        'type' => 'string',
                        'description' => 'SEO meta description',
                    ],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'delete_post',
            'description' => 'Delete a blog post (soft delete). The post can be restored later.',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'integer',
                        'description' => 'Post ID',
                    ],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'publish_post',
            'description' => 'Publish a draft post. Sets status to published and published_at to now.',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'integer',
                        'description' => 'Post ID',
                    ],
                ],
                'required' => ['id'],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'unpublish_post',
            'description' => 'Unpublish a published post. Sets status back to draft.',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'integer',
                        'description' => 'Post ID',
                    ],
                ],
                'required' => ['id'],
            ],
        ];

        // Category Tools
        $this->toolDefinitions[] = [
            'name' => 'list_categories',
            'description' => 'List all blog categories',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'page' => [
                        'type' => 'integer',
                        'description' => 'Page number',
                    ],
                    'per_page' => [
                        'type' => 'integer',
                        'description' => 'Items per page (max: 100)',
                    ],
                ],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_category',
            'description' => 'Get a single category by ID',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'integer',
                        'description' => 'Category ID',
                    ],
                ],
                'required' => ['id'],
            ],
        ];

        // Tag Tools
        $this->toolDefinitions[] = [
            'name' => 'list_tags',
            'description' => 'List all blog tags',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'page' => [
                        'type' => 'integer',
                        'description' => 'Page number',
                    ],
                    'per_page' => [
                        'type' => 'integer',
                        'description' => 'Items per page (max: 100)',
                    ],
                ],
            ],
        ];

        $this->toolDefinitions[] = [
            'name' => 'get_tag',
            'description' => 'Get a single tag by ID',
            'inputSchema' => [
                'type' => 'object',
                'properties' => [
                    'id' => [
                        'type' => 'integer',
                        'description' => 'Tag ID',
                    ],
                ],
                'required' => ['id'],
            ],
        ];
    }

    public function list(): array
    {
        return ['tools' => $this->toolDefinitions];
    }

    public function call(string $name, array $arguments = []): array
    {
        if (! isset($this->toolExecutors[$name])) {
            return [
                'content' => [
                    [
                        'type' => 'text',
                        'text' => json_encode([
                            'success' => false,
                            'error' => "Unknown tool: {$name}",
                        ]),
                    ],
                ],
                'isError' => true,
            ];
        }

        $result = $this->errorHandler->wrap(
            fn () => call_user_func($this->toolExecutors[$name], $arguments),
            "tool:{$name}"
        );

        $isError = isset($result['success']) && $result['success'] === false;

        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => json_encode($result),
                ],
            ],
            'isError' => $isError,
        ];
    }
}
