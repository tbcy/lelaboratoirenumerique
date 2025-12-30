<?php

namespace App\Exceptions\Mcp;

class DatabaseException extends McpException
{
    protected int $mcpCode = -32004;

    protected string $mcpMessage = 'Database error';

    public function __construct(string $message, ?\Throwable $previous = null)
    {
        $this->mcpMessage = $message;

        parent::__construct($message, 0, $previous);
    }

    public static function make(string $message, ?\Throwable $previous = null): self
    {
        return new self($message, $previous);
    }
}
