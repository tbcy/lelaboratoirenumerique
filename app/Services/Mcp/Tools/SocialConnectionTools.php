<?php

namespace App\Services\Mcp\Tools;

use App\Http\Requests\Mcp\SocialConnection\CreateSocialConnectionRequest;
use App\Http\Requests\Mcp\SocialConnection\UpdateSocialConnectionRequest;
use App\Models\SocialConnection;
use App\Services\Mcp\AuditLogService;
use Illuminate\Support\Facades\Validator;

class SocialConnectionTools
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    /**
     * Create a new social connection
     *
     * @param array $args Social connection data
     * @return array
     */
    public function create(array $args): array
    {
        // Validate input
        $request = new CreateSocialConnectionRequest();
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

            $connection = SocialConnection::create([
                'name' => $validated['name'],
                'platform' => $validated['platform'],
                'credentials' => $validated['credentials'] ?? [],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Don't log credentials for security
            $logData = $connection->toArray();
            unset($logData['credentials']);

            $this->auditLog->log(
                'create',
                'social_connection',
                $connection->id,
                $logData
            );

            return [
                'success' => true,
                'message' => 'Social connection created successfully',
                'id' => $connection->id,
                'platform' => $connection->platform,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create social connection',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update an existing social connection
     *
     * @param array $args Social connection data with id
     * @return array
     */
    public function update(array $args): array
    {
        // Validate input
        $request = new UpdateSocialConnectionRequest();
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
            $connection = SocialConnection::findOrFail($validated['id']);
            $oldValues = $connection->toArray();
            unset($oldValues['credentials']); // Don't log credentials

            $fillable = ['name', 'platform', 'credentials', 'is_active'];
            $updates = array_intersect_key($validated, array_flip($fillable));

            $connection->update($updates);

            // Don't log credentials for security
            $logUpdates = $updates;
            if (isset($logUpdates['credentials'])) {
                $logUpdates['credentials'] = '[REDACTED]';
            }

            $this->auditLog->log(
                'update',
                'social_connection',
                $connection->id,
                [
                    'old' => array_intersect_key($oldValues, $updates),
                    'new' => $logUpdates,
                ]
            );

            return [
                'success' => true,
                'message' => 'Social connection updated successfully',
                'id' => $connection->id,
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Social connection not found',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update social connection',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a social connection
     *
     * @param array $args Arguments with 'id' key
     * @return array
     */
    public function delete(array $args): array
    {
        $validator = Validator::make($args, [
            'id' => 'required|integer|exists:social_connections,id',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        try {
            $connection = SocialConnection::findOrFail($args['id']);

            // Check if connection is used by any posts
            $postsCount = \App\Models\SocialPost::whereJsonContains('connection_ids', $connection->id)->count();
            if ($postsCount > 0) {
                return [
                    'success' => false,
                    'message' => "Cannot delete: connection is used by {$postsCount} social post(s)",
                ];
            }

            $connectionData = $connection->toArray();
            unset($connectionData['credentials']);

            $connection->delete();

            $this->auditLog->log(
                'delete',
                'social_connection',
                $args['id'],
                $connectionData
            );

            return [
                'success' => true,
                'message' => 'Social connection deleted successfully',
                'id' => $args['id'],
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Social connection not found',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete social connection',
                'error' => $e->getMessage(),
            ];
        }
    }
}
