<?php

namespace App\Services\Mcp;

use App\Exceptions\Mcp\DatabaseException;
use App\Exceptions\Mcp\McpException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ErrorHandlingService
{
    public function wrap(callable $callback, string $context = ''): array
    {
        try {
            return $callback();
        } catch (McpException $e) {
            Log::warning("MCP Exception [{$context}]: {$e->getMessage()}", $e->getData());

            return [
                'success' => false,
                'error' => $e->toArray(),
            ];
        } catch (QueryException $e) {
            Log::error("Database Exception [{$context}]: {$e->getMessage()}");

            $dbException = DatabaseException::make('Database operation failed');

            return [
                'success' => false,
                'error' => $dbException->toArray(),
            ];
        } catch (\Throwable $e) {
            Log::error("Unexpected Exception [{$context}]: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error: '.$e->getMessage(),
                ],
            ];
        }
    }
}
