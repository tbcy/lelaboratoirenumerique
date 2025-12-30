<?php

return [
    'api_key' => env('MCP_API_KEY'),
    'protocol_version' => '2025-03-26',
    'server' => [
        'name' => 'lelaboratoirenumerique-mcp-server',
        'version' => '1.0.0',
    ],
    'session_enabled' => env('MCP_SESSION_ENABLED', true),
    'session_ttl' => env('MCP_SESSION_TTL', 1440),
];
