<?php

namespace App\Services\Notion;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

class NotionClient
{
    private string $token;
    private string $version;
    private string $baseUrl = 'https://api.notion.com/v1';
    private int $rateLimitDelay = 350000; // 350ms in microseconds

    public function __construct()
    {
        $token = config('services.notion.token');
        $version = config('services.notion.version');

        if (empty($token)) {
            throw new \RuntimeException('Notion API token is not configured. Please set NOTION_TOKEN in your .env file.');
        }

        $this->token = $token;
        $this->version = $version ?? '2022-06-28';
    }

    /**
     * Get a page by ID.
     */
    public function getPage(string $pageId): array
    {
        return $this->request('GET', "/pages/{$pageId}");
    }

    /**
     * Get block children with pagination support.
     */
    public function getBlockChildren(string $blockId, ?string $cursor = null, int $pageSize = 100): array
    {
        $query = ['page_size' => $pageSize];

        if ($cursor) {
            $query['start_cursor'] = $cursor;
        }

        return $this->request('GET', "/blocks/{$blockId}/children", $query);
    }

    /**
     * Get all block children, handling pagination automatically.
     */
    public function getAllBlockChildren(string $blockId): array
    {
        $allBlocks = [];
        $cursor = null;

        do {
            $response = $this->getBlockChildren($blockId, $cursor);
            $allBlocks = array_merge($allBlocks, $response['results'] ?? []);
            $cursor = $response['next_cursor'] ?? null;
        } while ($response['has_more'] ?? false);

        return $allBlocks;
    }

    /**
     * Get a database by ID.
     */
    public function getDatabase(string $databaseId): array
    {
        return $this->request('GET', "/databases/{$databaseId}");
    }

    /**
     * Query a database to get all its entries (pages).
     */
    public function queryDatabase(string $databaseId, ?string $cursor = null, int $pageSize = 100): array
    {
        $body = ['page_size' => $pageSize];

        if ($cursor) {
            $body['start_cursor'] = $cursor;
        }

        return $this->request('POST', "/databases/{$databaseId}/query", [], $body);
    }

    /**
     * Get all entries from a database, handling pagination.
     */
    public function getAllDatabaseEntries(string $databaseId): array
    {
        $allEntries = [];
        $cursor = null;

        do {
            $response = $this->queryDatabase($databaseId, $cursor);
            $allEntries = array_merge($allEntries, $response['results'] ?? []);
            $cursor = $response['next_cursor'] ?? null;
        } while ($response['has_more'] ?? false);

        return $allEntries;
    }

    /**
     * Detect if an ID is a page or a database.
     */
    public function detectType(string $id): string
    {
        try {
            $this->getPage($id);
            return 'page';
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'is a database')) {
                return 'database';
            }
            throw $e;
        }
    }

    /**
     * Search for pages.
     */
    public function search(string $query = '', ?string $filter = 'page'): array
    {
        $body = [];

        if ($query) {
            $body['query'] = $query;
        }

        if ($filter) {
            $body['filter'] = [
                'value' => $filter,
                'property' => 'object',
            ];
        }

        return $this->request('POST', '/search', [], $body);
    }

    /**
     * Extract the title from a page object.
     */
    public function extractPageTitle(array $page): string
    {
        $properties = $page['properties'] ?? [];

        // Try common title property names
        foreach (['title', 'Title', 'Name', 'name'] as $key) {
            if (isset($properties[$key])) {
                $titleProperty = $properties[$key];

                if ($titleProperty['type'] === 'title' && !empty($titleProperty['title'])) {
                    return $this->extractPlainText($titleProperty['title']);
                }
            }
        }

        // Fallback: search for any title type property
        foreach ($properties as $property) {
            if (($property['type'] ?? '') === 'title' && !empty($property['title'])) {
                return $this->extractPlainText($property['title']);
            }
        }

        return 'Untitled';
    }

    /**
     * Extract plain text from rich text array.
     */
    public function extractPlainText(array $richText): string
    {
        $text = '';

        foreach ($richText as $item) {
            $text .= $item['plain_text'] ?? $item['text']['content'] ?? '';
        }

        return $text;
    }

    /**
     * Make an API request to Notion.
     */
    private function request(string $method, string $endpoint, array $query = [], array $body = []): array
    {
        usleep($this->rateLimitDelay);

        $url = $this->baseUrl . $endpoint;

        try {
            $request = Http::withHeaders([
                'Authorization' => "Bearer {$this->token}",
                'Notion-Version' => $this->version,
                'Content-Type' => 'application/json',
            ])->timeout(30);

            if (!empty($query)) {
                $request = $request->withQueryParameters($query);
            }

            $response = match ($method) {
                'GET' => $request->get($url),
                'POST' => $request->post($url, $body),
                'PATCH' => $request->patch($url, $body),
                'DELETE' => $request->delete($url),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
            };

            if ($response->status() === 429) {
                // Rate limited - wait and retry
                $retryAfter = $response->header('Retry-After', 1);
                Log::warning("Notion rate limit hit. Waiting {$retryAfter} seconds.");
                sleep((int) $retryAfter);

                return $this->request($method, $endpoint, $query, $body);
            }

            if (!$response->successful()) {
                $error = $response->json();
                Log::error('Notion API error', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'error' => $error,
                ]);

                throw new \RuntimeException(
                    "Notion API error: " . ($error['message'] ?? 'Unknown error') .
                    " (Code: " . ($error['code'] ?? 'unknown') . ")"
                );
            }

            return $response->json();
        } catch (RequestException $e) {
            Log::error('Notion API request failed', [
                'endpoint' => $endpoint,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
