<?php

namespace App\Exceptions\Mcp;

class ValidationException extends McpException
{
    protected int $mcpCode = -32602;

    protected string $mcpMessage = 'Invalid params';

    public function __construct(array $errors)
    {
        $this->mcpMessage = 'Validation failed';
        $this->data = ['errors' => $errors];

        parent::__construct($this->mcpMessage);
    }

    public static function make(array $errors): self
    {
        return new self($errors);
    }
}
