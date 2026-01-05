<?php

namespace App\Http\Requests\Mcp\Quote;

use App\Http\Requests\Mcp\BaseToolRequest;

class ConvertQuoteToInvoiceRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:quotes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The quote ID is required.',
            'id.exists' => 'The specified quote does not exist.',
        ];
    }
}
