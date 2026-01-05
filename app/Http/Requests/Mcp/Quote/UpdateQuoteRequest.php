<?php

namespace App\Http\Requests\Mcp\Quote;

use App\Http\Requests\Mcp\BaseToolRequest;

class UpdateQuoteRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:quotes,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'subject' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:draft,sent,accepted,rejected,expired'],
            'introduction' => ['nullable', 'string'],
            'conclusion' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'lines' => ['nullable', 'array'],
            'lines.*.description' => ['required_with:lines', 'string'],
            'lines.*.quantity' => ['required_with:lines', 'numeric', 'min:0'],
            'lines.*.unit' => ['nullable', 'string'],
            'lines.*.unit_price' => ['required_with:lines', 'numeric', 'min:0'],
            'lines.*.vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The quote ID is required.',
            'id.exists' => 'The specified quote does not exist.',
            'subject.max' => 'The subject must not exceed 255 characters.',
            'project_id.exists' => 'The selected project does not exist.',
        ];
    }
}
