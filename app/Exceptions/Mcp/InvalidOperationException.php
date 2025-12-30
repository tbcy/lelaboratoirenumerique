<?php

namespace App\Exceptions\Mcp;

class InvalidOperationException extends McpException
{
    protected int $mcpCode = -32003;

    protected string $mcpMessage = 'Invalid operation';

    public function __construct(string $message, array $data = [])
    {
        $this->mcpMessage = $message;
        $this->data = $data;

        parent::__construct($message);
    }

    public static function make(string $message, array $data = []): self
    {
        return new self($message, $data);
    }
}
