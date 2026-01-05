<?php

namespace App\Http\Requests\Mcp\Invoice;

use App\Http\Requests\Mcp\BaseToolRequest;

class CreateInvoiceRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'number' => ['nullable', 'string', 'unique:invoices,number'],
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'quote_id' => ['nullable', 'integer', 'exists:quotes,id'],
            'subject' => ['required', 'string', 'max:255'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'status' => ['nullable', 'string', 'in:draft,sent,paid,partial,overdue,cancelled'],
            'introduction' => ['nullable', 'string'],
            'conclusion' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
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
            'client_id.required' => 'A client is required for the invoice.',
            'client_id.exists' => 'The selected client does not exist.',
            'subject.required' => 'The invoice subject is required.',
            'subject.max' => 'The subject must not exceed 255 characters.',
            'issue_date.required' => 'The issue date is required.',
            'due_date.required' => 'The due date is required.',
            'due_date.after_or_equal' => 'The due date must be on or after the issue date.',
            'number.unique' => 'This invoice number is already in use.',
            'lines.*.description.required' => 'Each line must have a description.',
            'lines.*.quantity.required' => 'Each line must have a quantity.',
            'lines.*.unit_price.required' => 'Each line must have a unit price.',
        ];
    }
}
