<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class McpAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = config('mcp.api_key');
        $token = $request->bearerToken() ?? $request->header('X-API-Key');

        if (empty($token) || $token !== $apiKey) {
            return response()->json([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32600,
                    'message' => 'Unauthorized: Invalid API key',
                ],
                'id' => $request->input('id'),
            ], 401);
        }

        return $next($request);
    }
}
