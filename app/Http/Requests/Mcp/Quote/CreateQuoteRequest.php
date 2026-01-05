<?php

namespace App\Http\Requests\Mcp\Quote;

use App\Http\Requests\Mcp\BaseToolRequest;

class CreateQuoteRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'number' => ['nullable', 'string', 'unique:quotes,number'],
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'subject' => ['required', 'string', 'max:255'],
            'issue_date' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after_or_equal:issue_date'],
            'status' => ['nullable', 'string', 'in:draft,sent,accepted,rejected,expired'],
            'introduction' => ['nullable', 'string'],
            'conclusion' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'lines' => ['nullable', 'array'],
            'lines.*.description' => ['required', 'string'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0'],
            'lines.*.unit' => ['nullable', 'string'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'A client is required for the quote.',
            'client_id.exists' => 'The selected client does not exist.',
            'subject.required' => 'The quote subject is required.',
            'subject.max' => 'The subject must not exceed 255 characters.',
            'issue_date.required' => 'The issue date is required.',
            'valid_until.required' => 'The validity date is required.',
            'valid_until.after_or_equal' => 'The validity date must be on or after the issue date.',
            'number.unique' => 'This quote number is already in use.',
        ];
    }
}
