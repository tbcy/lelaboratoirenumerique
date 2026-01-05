<?php

namespace App\Services\Mcp\Tools;

use App\Http\Requests\Mcp\Quote\CreateQuoteRequest;
use App\Http\Requests\Mcp\Quote\UpdateQuoteRequest;
use App\Http\Requests\Mcp\Quote\ConvertQuoteToInvoiceRequest;
use App\Models\Company;
use App\Models\Quote;
use App\Models\QuoteLine;
use App\Services\Mcp\AuditLogService;
use App\Services\Mcp\CalculationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QuoteTools
{
    public function __construct(
        private AuditLogService $auditLog,
        private CalculationService $calculator
    ) {}

    /**
     * Create a new quote
     *
     * @param array $args Quote data with lines
     * @return array
     */
    public function create(array $args): array
    {
        // Validate input
        $request = new CreateQuoteRequest();
        $validator = Validator::make($args, $request->rules(), $request->messages());

        if (method_exists($request, 'withValidator')) {
            $request->withValidator($validator);
        }

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        DB::beginTransaction();

        try {
            $validated = $validator->validated();

            // Calculate totals server-side for lines
            $lines = [];
            $totals = ['total_ht' => 0, 'total_vat' => 0, 'total_ttc' => 0];

            if (isset($validated['lines']) && is_array($validated['lines'])) {
                $result = $this->calculator->processQuoteLines($validated['lines']);
                $lines = $result['lines'];
                $totals = $result['totals'];
            }

            // Auto-generate quote number if not provided
            // Note: quote_number alias is converted to 'number' in ToolsHandler::normalizeAliases()
            $quoteNumber = $validated['number'] ?? null;
            if (empty($quoteNumber)) {
                $company = Company::first();
                $quoteNumber = $company ? $company->generateQuoteNumber() : 'D-' . date('Y') . '-' . str_pad(Quote::count() + 1, 4, '0', STR_PAD_LEFT);
            }

            $quote = Quote::create([
                'number' => $quoteNumber,
                'client_id' => $validated['client_id'],
                'project_id' => $validated['project_id'] ?? null,
                'subject' => $validated['subject'] ?? null,
                'introduction' => $validated['introduction'] ?? null,
                'conclusion' => $validated['conclusion'] ?? null,
                'status' => $validated['status'] ?? 'draft',
                'issue_date' => $validated['issue_date'] ?? now(),
                'validity_date' => $validated['validity_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'total_ht' => $totals['total_ht'],
                'total_vat' => $totals['total_vat'],
                'total_ttc' => $totals['total_ttc'],
            ]);

            // Create quote lines with calculated totals
            foreach ($lines as $index => $lineData) {
                QuoteLine::create([
                    'quote_id' => $quote->id,
                    'catalog_item_id' => $lineData['catalog_item_id'] ?? null,
                    'description' => $lineData['description'],
                    'quantity' => $lineData['quantity'],
                    'unit' => $lineData['unit'] ?? 'unit',
                    'unit_price' => $lineData['unit_price'],
                    'vat_rate' => $lineData['vat_rate'],
                    'total_ht' => $lineData['total_ht'],  // Calculated server-side
                    'total_vat' => $lineData['total_vat'], // Calculated server-side
                    'total_ttc' => $lineData['total_ttc'], // Calculated server-side
                    'sort_order' => $index,
                ]);
            }

            DB::commit();

            $this->auditLog->log(
                'create',
                'quote',
                $quote->id,
                $quote->toArray()
            );

            return [
                'success' => true,
                'message' => 'Quote created successfully',
                'id' => $quote->id,
                'number' => $quote->number,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create quote',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update an existing quote
     *
     * @param array $args Quote data with id
     * @return array
     */
    public function update(array $args): array
    {
        // Validate input
        $request = new UpdateQuoteRequest();
        $validator = Validator::make($args, $request->rules(), $request->messages());

        if (method_exists($request, 'withValidator')) {
            $request->withValidator($validator);
        }

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        DB::beginTransaction();

        try {
            $validated = $validator->validated();
            $quote = Quote::findOrFail($validated['id']);
            $oldValues = $quote->toArray();

            // If lines are being updated, recalculate totals
            if (isset($validated['lines'])) {
                // Delete existing lines and create new ones with calculated totals
                $quote->lines()->delete();

                $result = $this->calculator->processQuoteLines($validated['lines']);
                $lines = $result['lines'];
                $totals = $result['totals'];

                foreach ($lines as $index => $lineData) {
                    QuoteLine::create([
                        'quote_id' => $quote->id,
                        'catalog_item_id' => $lineData['catalog_item_id'] ?? null,
                        'description' => $lineData['description'],
                        'quantity' => $lineData['quantity'],
                        'unit' => $lineData['unit'] ?? 'unit',
                        'unit_price' => $lineData['unit_price'],
                        'vat_rate' => $lineData['vat_rate'],
                        'total_ht' => $lineData['total_ht'],
                        'total_vat' => $lineData['total_vat'],
                        'total_ttc' => $lineData['total_ttc'],
                        'sort_order' => $index,
                    ]);
                }

                $validated['total_ht'] = $totals['total_ht'];
                $validated['total_vat'] = $totals['total_vat'];
                $validated['total_ttc'] = $totals['total_ttc'];
            }

            $fillable = [
                'number', 'client_id', 'project_id', 'subject',
                'introduction', 'conclusion', 'status', 'issue_date',
                'validity_date', 'notes', 'total_ht', 'total_vat', 'total_ttc'
            ];

            $updates = array_intersect_key($validated, array_flip($fillable));
            $quote->update($updates);

            DB::commit();

            $this->auditLog->log(
                'update',
                'quote',
                $quote->id,
                [
                    'old' => array_intersect_key($oldValues, $updates),
                    'new' => $updates,
                ]
            );

            return [
                'success' => true,
                'message' => 'Quote updated successfully',
                'id' => $quote->id,
                'number' => $quote->number,
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Quote not found',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to update quote',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a quote (soft delete)
     *
     * @param int $id Quote ID
     * @return array
     */
    public function delete(int $id): array
    {
        try {
            $quote = Quote::findOrFail($id);

            // Check if quote has already been converted to invoice
            if ($quote->invoice !== null) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete quote that has been converted to an invoice',
                ];
            }

            $quoteNumber = $quote->number;
            $quote->delete();

            $this->auditLog->log(
                'delete',
                'quote',
                $id,
                [
                    'number' => $quoteNumber,
                    'client_id' => $quote->client_id,
                ]
            );

            return [
                'success' => true,
                'message' => 'Quote deleted successfully',
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Quote not found',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete quote',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Convert a quote to an invoice
     *
     * @param array $args Arguments with 'id' key
     * @return array
     */
    public function convertToInvoice(array $args): array
    {
        // Validate input
        $request = new ConvertQuoteToInvoiceRequest();
        $validator = Validator::make($args, $request->rules(), $request->messages());

        if (method_exists($request, 'withValidator')) {
            $request->withValidator($validator);
        }

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        DB::beginTransaction();

        try {
            $validated = $validator->validated();
            $quote = Quote::with('lines')->findOrFail($validated['id']);

            // Use the model's convertToInvoice method
            $invoice = $quote->convertToInvoice();

            DB::commit();

            $this->auditLog->log(
                'convert_to_invoice',
                'quote',
                $quote->id,
                [
                    'quote_number' => $quote->number,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->number,
                ]
            );

            return [
                'success' => true,
                'message' => "Quote {$quote->number} converted to invoice {$invoice->number}",
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->number,
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Quote not found',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to convert quote to invoice',
                'error' => $e->getMessage(),
            ];
        }
    }
}
