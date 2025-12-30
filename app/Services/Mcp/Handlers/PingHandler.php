<?php

namespace App\Services\Mcp\Handlers;

class PingHandler
{
    public function handle(): array
    {
        return [
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'server' => config('mcp.server.name'),
        ];
    }
}
