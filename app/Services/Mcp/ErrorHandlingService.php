<?php

namespace App\Services\Mcp;

use App\Exceptions\Mcp\DatabaseException;
use App\Exceptions\Mcp\McpException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class ErrorHandlingService
{
    /**
     * Validate that required parameters are present
     */
    public function validateRequiredParams(array $params, array $required, string $context = ''): void
    {
        $missing = [];
        foreach ($required as $key) {
            if (! isset($params[$key]) || $params[$key] === '') {
                $missing[] = $key;
            }
        }

        if (! empty($missing)) {
            throw new \InvalidArgumentException(
                "Missing required parameters for {$context}: ".implode(', ', $missing)
            );
        }
    }

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
