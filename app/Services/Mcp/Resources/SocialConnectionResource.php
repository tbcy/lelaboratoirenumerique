<?php

namespace App\Services\Mcp\Resources;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\SocialConnection;
use App\Services\Mcp\ErrorHandlingService;
use App\Services\Mcp\PaginationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SocialConnectionResource
{
    public function __construct(
        private PaginationService $pagination,
        private ErrorHandlingService $errorHandler
    ) {}

    /**
     * List social connections with optional filters and pagination
     *
     * @param  array  $filters  Optional filters: platform, is_active, page, per_page
     */
    public function list(array $filters = []): array
    {
        $query = SocialConnection::query();

        // Filter by platform
        if (isset($filters['platform'])) {
            $query->where('platform', $filters['platform']);
        }

        // Filter by is_active
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        $query->orderBy('created_at', 'desc');

        // Use pagination service
        $result = $this->pagination->paginate($query, $filters);

        return [
            'connections' => collect($result['data'])->map(function ($connection) {
                return [
                    'id' => $connection->id,
                    'name' => $connection->name,
                    'platform' => $connection->platform,
                    'display_name' => $connection->display_name,
                    'is_active' => $connection->is_active,
                    'last_used_at' => $connection->last_used_at,
                    'created_at' => $connection->created_at,
                    'updated_at' => $connection->updated_at,
                ];
            })->toArray(),
            'pagination' => $result['pagination'],
        ];
    }

    /**
     * Get a single social connection
     * Note: credentials are hidden by the model
     *
     * @throws ResourceNotFoundException
     */
    public function get(int $id): array
    {
        try {
            $connection = SocialConnection::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('social_connection', $id);
        }

        return [
            'id' => $connection->id,
            'name' => $connection->name,
            'platform' => $connection->platform,
            'display_name' => $connection->display_name,
            'is_active' => $connection->is_active,
            'last_used_at' => $connection->last_used_at,
            'created_at' => $connection->created_at,
            'updated_at' => $connection->updated_at,
            // Note: credentials are intentionally hidden for security
        ];
    }
}
