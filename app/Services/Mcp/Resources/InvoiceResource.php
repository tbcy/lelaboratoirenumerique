<?php

namespace App\Services\Mcp\Resources;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\Invoice;
use App\Services\Mcp\ErrorHandlingService;
use App\Services\Mcp\PaginationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InvoiceResource
{
    public function __construct(
        private PaginationService $pagination,
        private ErrorHandlingService $errorHandler
    ) {}

    /**
     * List invoices with optional filters and pagination
     *
     * @param  array  $filters  Optional filters: status, client_id, unpaid, page, per_page
     */
    public function list(array $filters = []): array
    {
        $query = Invoice::with(['client', 'quote', 'project']);

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by client_id
        if (isset($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        // Filter unpaid invoices
        if (isset($filters['unpaid']) && $filters['unpaid']) {
            $query->whereIn('status', ['sent', 'partial', 'overdue']);
        }

        $query->orderBy('created_at', 'desc');

        // Use pagination service
        $result = $this->pagination->paginate($query, $filters);

        return [
            'invoices' => collect($result['data'])->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'number' => $invoice->number,
                    'client_id' => $invoice->client_id,
                    'client_name' => $invoice->client?->display_name,
                    'quote_id' => $invoice->quote_id,
                    'quote_number' => $invoice->quote?->number,
                    'project_id' => $invoice->project_id,
                    'project_name' => $invoice->project?->name,
                    'subject' => $invoice->subject,
                    'status' => $invoice->status,
                    'issue_date' => $invoice->issue_date,
                    'due_date' => $invoice->due_date,
                    'total_ht' => (float) $invoice->total_ht,
                    'total_vat' => (float) $invoice->total_vat,
                    'total_ttc' => (float) $invoice->total_ttc,
                    'amount_paid' => (float) $invoice->amount_paid,
                    'amount_due' => (float) $invoice->amount_due,
                    'is_overdue' => $invoice->is_overdue,
                    'paid_at' => $invoice->paid_at,
                    'created_at' => $invoice->created_at,
                    'updated_at' => $invoice->updated_at,
                ];
            })->toArray(),
            'pagination' => $result['pagination'],
        ];
    }

    /**
     * Get a single invoice with lines
     *
     * @throws ResourceNotFoundException
     */
    public function get(int $id): array
    {
        try {
            $invoice = Invoice::with(['client', 'quote', 'project', 'lines'])
                ->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('invoice', $id);
        }

        return [
            'id' => $invoice->id,
            'number' => $invoice->number,
            'client_id' => $invoice->client_id,
            'client' => $invoice->client ? [
                'id' => $invoice->client->id,
                'display_name' => $invoice->client->display_name,
                'email' => $invoice->client->email,
            ] : null,
            'quote_id' => $invoice->quote_id,
            'quote' => $invoice->quote ? [
                'id' => $invoice->quote->id,
                'number' => $invoice->quote->number,
            ] : null,
            'project_id' => $invoice->project_id,
            'project' => $invoice->project ? [
                'id' => $invoice->project->id,
                'name' => $invoice->project->name,
            ] : null,
            'subject' => $invoice->subject,
            'introduction' => $invoice->introduction,
            'conclusion' => $invoice->conclusion,
            'status' => $invoice->status,
            'issue_date' => $invoice->issue_date,
            'due_date' => $invoice->due_date,
            'total_ht' => (float) $invoice->total_ht,
            'total_vat' => (float) $invoice->total_vat,
            'total_ttc' => (float) $invoice->total_ttc,
            'amount_paid' => (float) $invoice->amount_paid,
            'amount_due' => (float) $invoice->amount_due,
            'is_overdue' => $invoice->is_overdue,
            'paid_at' => $invoice->paid_at,
            'notes' => $invoice->notes,
            'created_at' => $invoice->created_at,
            'updated_at' => $invoice->updated_at,
            'lines' => $invoice->lines->map(fn ($line) => [
                'id' => $line->id,
                'description' => $line->description,
                'quantity' => (float) $line->quantity,
                'unit' => $line->unit,
                'unit_price' => (float) $line->unit_price,
                'vat_rate' => (float) $line->vat_rate,
                'total_ht' => (float) $line->total_ht,
                'total_vat' => (float) $line->total_vat,
                'total_ttc' => (float) $line->total_ttc,
                'sort_order' => $line->sort_order,
            ])->toArray(),
        ];
    }
}
