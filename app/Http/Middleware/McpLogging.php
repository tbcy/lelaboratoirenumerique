<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class McpLogging
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        Log::channel('mcp')->info('MCP Request', [
            'method' => $request->input('method'),
            'id' => $request->input('id'),
            'duration_ms' => $duration,
            'status' => $response->getStatusCode(),
        ]);

        return $response;
    }
}
