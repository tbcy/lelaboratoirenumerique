<?php

namespace App\Exceptions\Mcp;

use Exception;

abstract class McpException extends Exception
{
    protected int $mcpCode;

    protected string $mcpMessage;

    protected array $data = [];

    public function getMcpCode(): int
    {
        return $this->mcpCode;
    }

    public function getMcpMessage(): string
    {
        return $this->mcpMessage;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return [
            'code' => $this->mcpCode,
            'message' => $this->mcpMessage,
            'data' => $this->data ?: null,
        ];
    }
}
