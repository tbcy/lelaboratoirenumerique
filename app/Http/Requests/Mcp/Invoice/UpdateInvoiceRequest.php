<?php

namespace App\Http\Requests\Mcp\Invoice;

use App\Http\Requests\Mcp\BaseToolRequest;
use Illuminate\Validation\Rule;

class UpdateInvoiceRequest extends BaseToolRequest
{
    public function rules(): array
    {
        $invoiceId = request()->input('id');

        return [
            'id' => ['required', 'integer', 'exists:invoices,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'subject' => ['nullable', 'string', 'max:255'],
            'issue_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:draft,sent,paid,partial,overdue,cancelled'],
            'introduction' => ['nullable', 'string'],
            'conclusion' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
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
            'id.required' => 'The invoice ID is required.',
            'id.exists' => 'The specified invoice does not exist.',
            'subject.max' => 'The subject must not exceed 255 characters.',
            'project_id.exists' => 'The selected project does not exist.',
            'lines.*.description.required_with' => 'Each line must have a description.',
            'lines.*.quantity.required_with' => 'Each line must have a quantity.',
            'lines.*.unit_price.required_with' => 'Each line must have a unit price.',
        ];
    }
}
