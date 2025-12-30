<?php

namespace App\Http\Requests\Mcp;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseToolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [];
    }
}
