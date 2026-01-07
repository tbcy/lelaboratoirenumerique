<?php

namespace App\Services\Mcp\Handlers;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Services\Mcp\Resources\CategoryResource;
use App\Services\Mcp\Resources\PostResource;
use App\Services\Mcp\Resources\TagResource;

class ResourcesHandler
{
    private array $resourceProviders = [];

    public function __construct(
        private PostResource $postResource,
        private CategoryResource $categoryResource,
        private TagResource $tagResource
    ) {
        $this->resourceProviders = [
            'labo://posts' => [$this->postResource, 'read'],
            'labo://posts/*' => [$this->postResource, 'readSingle'],
            'labo://categories' => [$this->categoryResource, 'read'],
            'labo://categories/*' => [$this->categoryResource, 'readSingle'],
            'labo://tags' => [$this->tagResource, 'read'],
            'labo://tags/*' => [$this->tagResource, 'readSingle'],
        ];
    }

    public function list(): array
    {
        $resources = [];

        $resources = array_merge($resources, $this->postResource->listResources());
        $resources = array_merge($resources, $this->categoryResource->listResources());
        $resources = array_merge($resources, $this->tagResource->listResources());

        return ['resources' => $resources];
    }

    public function read(string $uri): array
    {
        foreach ($this->resourceProviders as $pattern => $handler) {
            if ($this->matchUri($pattern, $uri)) {
                $id = $this->extractId($pattern, $uri);

                return [
                    'contents' => [
                        [
                            'uri' => $uri,
                            'mimeType' => 'application/json',
                            'text' => json_encode($id ? $handler[0]->{$handler[1]}($id) : $handler[0]->{$handler[1]}(), JSON_INVALID_UTF8_SUBSTITUTE),
                        ],
                    ],
                ];
            }
        }

        throw ResourceNotFoundException::make('resource', $uri);
    }

    private function matchUri(string $pattern, string $uri): bool
    {
        if ($pattern === $uri) {
            return true;
        }

        if (str_ends_with($pattern, '/*')) {
            $basePattern = substr($pattern, 0, -2);

            return str_starts_with($uri, $basePattern.'/');
        }

        return false;
    }

    private function extractId(string $pattern, string $uri): ?int
    {
        if (! str_ends_with($pattern, '/*')) {
            return null;
        }

        $basePattern = substr($pattern, 0, -2);
        $idPart = substr($uri, strlen($basePattern) + 1);

        return is_numeric($idPart) ? (int) $idPart : null;
    }
}
