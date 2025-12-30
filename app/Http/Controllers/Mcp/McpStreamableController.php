<?php

namespace App\Http\Controllers\Mcp;

use App\Http\Controllers\Controller;
use App\Services\Mcp\Handlers\InitializeHandler;
use App\Services\Mcp\Handlers\PingHandler;
use App\Services\Mcp\Handlers\ResourcesHandler;
use App\Services\Mcp\Handlers\ToolsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class McpStreamableController extends Controller
{
    private ?string $sessionId = null;

    public function __construct(
        private InitializeHandler $initializeHandler,
        private PingHandler $pingHandler,
        private ToolsHandler $toolsHandler,
        private ResourcesHandler $resourcesHandler
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $data = $request->all();

        // Validate JSON-RPC format
        if (! isset($data['jsonrpc']) || $data['jsonrpc'] !== '2.0') {
            return $this->errorResponse(-32600, 'Invalid Request: Missing or invalid jsonrpc version', $data['id'] ?? null);
        }

        if (! isset($data['method'])) {
            return $this->errorResponse(-32600, 'Invalid Request: Missing method', $data['id'] ?? null);
        }

        $method = $data['method'];
        $params = $data['params'] ?? [];
        $id = $data['id'] ?? null;

        // Handle session
        $this->handleSession($request);

        // Route to appropriate handler
        try {
            $result = match ($method) {
                'initialize' => $this->handleInitialize($params),
                'notifications/initialized' => $this->handleInitialized(),
                'ping' => $this->pingHandler->handle(),
                'tools/list' => $this->toolsHandler->list(),
                'tools/call' => $this->handleToolCall($params),
                'resources/list' => $this->resourcesHandler->list(),
                'resources/read' => $this->handleResourceRead($params),
                default => throw new \Exception("Method not found: {$method}"),
            };

            // Notifications don't return a response
            if ($method === 'notifications/initialized') {
                return response()->json([
                    'jsonrpc' => '2.0',
                    'result' => [],
                    'id' => $id,
                ]);
            }

            return $this->successResponse($result, $id);
        } catch (\Exception $e) {
            return $this->errorResponse(-32603, $e->getMessage(), $id);
        }
    }

    private function handleSession(Request $request): void
    {
        if (! config('mcp.session_enabled')) {
            return;
        }

        $this->sessionId = $request->header('Mcp-Session-Id');

        if (! $this->sessionId) {
            $this->sessionId = Str::uuid()->toString();
        }

        // Store session data
        Cache::put(
            "mcp_session:{$this->sessionId}",
            [
                'created_at' => now()->toIso8601String(),
                'last_activity' => now()->toIso8601String(),
            ],
            now()->addMinutes((int) config('mcp.session_ttl', 1440))
        );
    }

    private function handleInitialize(array $params): array
    {
        $result = $this->initializeHandler->handle($params);

        // Add session ID if sessions are enabled
        if ($this->sessionId && config('mcp.session_enabled')) {
            $result['_meta'] = [
                'sessionId' => $this->sessionId,
            ];
        }

        return $result;
    }

    private function handleInitialized(): array
    {
        // Update session to mark as initialized
        if ($this->sessionId && config('mcp.session_enabled')) {
            $sessionData = Cache::get("mcp_session:{$this->sessionId}", []);
            $sessionData['initialized'] = true;
            $sessionData['initialized_at'] = now()->toIso8601String();

            Cache::put(
                "mcp_session:{$this->sessionId}",
                $sessionData,
                now()->addMinutes((int) config('mcp.session_ttl', 1440))
            );
        }

        return [];
    }

    private function handleToolCall(array $params): array
    {
        $name = $params['name'] ?? null;
        $arguments = $params['arguments'] ?? [];

        if (! $name) {
            throw new \InvalidArgumentException('Tool name is required');
        }

        return $this->toolsHandler->call($name, $arguments);
    }

    private function handleResourceRead(array $params): array
    {
        $uri = $params['uri'] ?? null;

        if (! $uri) {
            throw new \InvalidArgumentException('Resource URI is required');
        }

        return $this->resourcesHandler->read($uri);
    }

    private function successResponse(array $result, mixed $id): JsonResponse
    {
        $response = [
            'jsonrpc' => '2.0',
            'result' => $result,
        ];

        if ($id !== null) {
            $response['id'] = $id;
        }

        $jsonResponse = response()->json($response);

        if ($this->sessionId && config('mcp.session_enabled')) {
            $jsonResponse->header('Mcp-Session-Id', $this->sessionId);
        }

        return $jsonResponse;
    }

    private function errorResponse(int $code, string $message, mixed $id): JsonResponse
    {
        return response()->json([
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
            'id' => $id,
        ]);
    }
}
