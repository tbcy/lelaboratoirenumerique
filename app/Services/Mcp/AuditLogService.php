<?php

namespace App\Services\Mcp;

use Illuminate\Support\Facades\Log;

class AuditLogService
{
    public function log(
        string $action,
        string $resourceType,
        int|string|null $resourceId = null,
        array $data = [],
        ?array $oldData = null
    ): void {
        $logData = [
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'timestamp' => now()->toIso8601String(),
        ];

        if (! empty($data)) {
            $logData['new_data'] = $data;
        }

        if ($oldData !== null) {
            $logData['old_data'] = $oldData;
        }

        Log::channel('mcp')->info("MCP Audit: {$action} {$resourceType}", $logData);
    }

    public function logCreate(string $resourceType, int|string $resourceId, array $data): void
    {
        $this->log('create', $resourceType, $resourceId, $data);
    }

    public function logUpdate(string $resourceType, int|string $resourceId, array $newData, array $oldData): void
    {
        $this->log('update', $resourceType, $resourceId, $newData, $oldData);
    }

    public function logDelete(string $resourceType, int|string $resourceId, array $data = []): void
    {
        $this->log('delete', $resourceType, $resourceId, $data);
    }
}
