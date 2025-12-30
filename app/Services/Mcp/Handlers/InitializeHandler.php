<?php

namespace App\Services\Mcp\Handlers;

class InitializeHandler
{
    public function handle(array $params = []): array
    {
        return [
            'protocolVersion' => config('mcp.protocol_version'),
            'capabilities' => [
                'tools' => [
                    'listChanged' => true,
                ],
                'resources' => [
                    'subscribe' => false,
                    'listChanged' => true,
                ],
            ],
            'serverInfo' => [
                'name' => config('mcp.server.name'),
                'version' => config('mcp.server.version'),
            ],
        ];
    }
}
