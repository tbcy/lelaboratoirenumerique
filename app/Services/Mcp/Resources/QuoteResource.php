<?php

namespace App\Services\Mcp\Resources;

use App\Exceptions\Mcp\ResourceNotFoundException;
use App\Models\Quote;
use App\Services\Mcp\ErrorHandlingService;
use App\Services\Mcp\PaginationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class QuoteResource
{
    public function __construct(
        private PaginationService $pagination,
        private ErrorHandlingService $errorHandler
    ) {}

    /**
     * List quotes with optional filters and pagination
     *
     * @param  array  $filters  Optional filters: status, client_id, page, per_page
     */
    public function list(array $filters = []): array
    {
        $query = Quote::with(['client', 'project']);

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by client_id
        if (isset($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        $query->orderBy('created_at', 'desc');

        // Use pagination service
        $result = $this->pagination->paginate($query, $filters);

        return [
            'quotes' => collect($result['data'])->map(function ($quote) {
                return [
                    'id' => $quote->id,
                    'number' => $quote->number,
                    'client_id' => $quote->client_id,
                    'client_name' => $quote->client?->display_name,
                    'project_id' => $quote->project_id,
                    'project_name' => $quote->project?->name,
                    'subject' => $quote->subject,
                    'status' => $quote->status,
                    'issue_date' => $quote->issue_date,
                    'validity_date' => $quote->validity_date,
                    'total_ht' => (float) $quote->total_ht,
                    'total_vat' => (float) $quote->total_vat,
                    'total_ttc' => (float) $quote->total_ttc,
                    'accepted_at' => $quote->accepted_at,
                    'has_invoice' => $quote->invoice !== null,
                    'created_at' => $quote->created_at,
                    'updated_at' => $quote->updated_at,
                ];
            })->toArray(),
            'pagination' => $result['pagination'],
        ];
    }

    /**
     * Get a single quote with lines
     *
     * @throws ResourceNotFoundException
     */
    public function get(int $id): array
    {
        try {
            $quote = Quote::with(['client', 'project', 'lines', 'invoice'])
                ->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw ResourceNotFoundException::make('quote', $id);
        }

        return [
            'id' => $quote->id,
            'number' => $quote->number,
            'client_id' => $quote->client_id,
            'client' => $quote->client ? [
                'id' => $quote->client->id,
                'display_name' => $quote->client->display_name,
                'email' => $quote->client->email,
            ] : null,
            'project_id' => $quote->project_id,
            'project' => $quote->project ? [
                'id' => $quote->project->id,
                'name' => $quote->project->name,
            ] : null,
            'subject' => $quote->subject,
            'introduction' => $quote->introduction,
            'conclusion' => $quote->conclusion,
            'status' => $quote->status,
            'issue_date' => $quote->issue_date,
            'validity_date' => $quote->validity_date,
            'total_ht' => (float) $quote->total_ht,
            'total_vat' => (float) $quote->total_vat,
            'total_ttc' => (float) $quote->total_ttc,
            'accepted_at' => $quote->accepted_at,
            'notes' => $quote->notes,
            'created_at' => $quote->created_at,
            'updated_at' => $quote->updated_at,
            'lines' => $quote->lines->map(fn ($line) => [
                'id' => $line->id,
                'catalog_item_id' => $line->catalog_item_id,
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
            'invoice' => $quote->invoice ? [
                'id' => $quote->invoice->id,
                'number' => $quote->invoice->number,
                'status' => $quote->invoice->status,
            ] : null,
        ];
    }
}
