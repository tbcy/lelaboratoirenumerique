<?php

namespace App\Exceptions\Mcp;

class ResourceNotFoundException extends McpException
{
    protected int $mcpCode = -32002;

    protected string $mcpMessage = 'Resource not found';

    public function __construct(string $resourceType, int|string $identifier)
    {
        $this->mcpMessage = "Resource not found: {$resourceType} with ID {$identifier}";
        $this->data = [
            'resource_type' => $resourceType,
            'identifier' => $identifier,
        ];

        parent::__construct($this->mcpMessage);
    }

    public static function make(string $resourceType, int|string $identifier): self
    {
        return new self($resourceType, $identifier);
    }
}
