<?php

namespace App\Services\Mcp\Tools;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Services\Mcp\AuditLogService;
use App\Services\Mcp\CalculationService;
use App\Http\Requests\Mcp\Invoice\CreateInvoiceRequest;
use App\Http\Requests\Mcp\Invoice\UpdateInvoiceRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoiceTools
{
    public function __construct(
        private AuditLogService $auditLog,
        private CalculationService $calculator
    ) {}

    /**
     * Create a new invoice with automatic total calculation
     *
     * @param array $args Invoice data with lines
     * @return array
     */
    public function create(array $args): array
    {
        // Validate input data using Form Request rules
        // Note: invoice_number alias is converted to 'number' in ToolsHandler::normalizeAliases()
        $validator = Validator::make($args, (new CreateInvoiceRequest)->rules());

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        $validated = $validator->validated();

        DB::beginTransaction();

        try {
            // Calculate line totals if lines are provided
            $lines = [];
            $totals = ['total_ht' => 0, 'total_vat' => 0, 'total_ttc' => 0];

            if (isset($validated['lines']) && is_array($validated['lines'])) {
                $result = $this->calculator->processInvoiceLines($validated['lines']);
                $lines = $result['lines'];
                $totals = $result['totals'];
            }

            // Auto-generate invoice number if not provided
            $invoiceNumber = $validated['number'] ?? null;
            if (empty($invoiceNumber)) {
                $company = Company::first();
                $invoiceNumber = $company ? $company->generateInvoiceNumber() : 'INV-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT);
            }

            // Create invoice with calculated totals
            $invoice = Invoice::create([
                'number' => $invoiceNumber,
                'client_id' => $validated['client_id'],
                'quote_id' => $validated['quote_id'] ?? null,
                'project_id' => $validated['project_id'] ?? null,
                'subject' => $validated['subject'],
                'introduction' => $args['introduction'] ?? null,
                'conclusion' => $args['conclusion'] ?? null,
                'status' => $validated['status'] ?? 'draft',
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'payment_terms' => $validated['payment_terms'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'total_ht' => $totals['total_ht'],
                'total_vat' => $totals['total_vat'],
                'total_ttc' => $totals['total_ttc'],
                'amount_paid' => 0,
            ]);

            // Create invoice lines with calculated totals
            foreach ($lines as $index => $lineData) {
                InvoiceLine::create([
                    'invoice_id' => $invoice->id,
                    'description' => $lineData['description'],
                    'quantity' => $lineData['quantity'],
                    'unit' => $lineData['unit'] ?? 'unit',
                    'unit_price' => $lineData['unit_price'],
                    'vat_rate' => $lineData['vat_rate'],
                    'total_ht' => $lineData['total_ht'],
                    'total_vat' => $lineData['total_ttc'] - $lineData['total_ht'],
                    'total_ttc' => $lineData['total_ttc'],
                    'sort_order' => $index,
                ]);
            }

            DB::commit();

            $this->auditLog->log(
                'create',
                'invoice',
                $invoice->id,
                [
                    'invoice' => $invoice->toArray(),
                    'lines_count' => count($lines),
                ]
            );

            return [
                'success' => true,
                'message' => 'Invoice created successfully',
                'id' => $invoice->id,
                'number' => $invoice->number,
                'total_ttc' => $invoice->total_ttc,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to create invoice',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update an existing invoice
     *
     * @param array $args Invoice data with id
     * @return array
     */
    public function update(array $args): array
    {
        // Validate input data using Form Request rules
        $validator = Validator::make($args, (new UpdateInvoiceRequest)->rules());

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ];
        }

        $validated = $validator->validated();

        DB::beginTransaction();

        try {
            $invoice = Invoice::findOrFail($validated['id']);
            $oldValues = $invoice->toArray();

            // Note: 'number' is NOT in fillable - invoice number cannot be changed after creation
            $fillable = [
                'client_id', 'quote_id', 'project_id',
                'subject', 'introduction', 'conclusion', 'status',
                'issue_date', 'due_date', 'payment_terms', 'notes'
            ];

            $updates = array_intersect_key($validated, array_flip($fillable));

            // Handle lines if provided
            if (isset($validated['lines']) && is_array($validated['lines'])) {
                // Calculate new line totals
                $result = $this->calculator->processInvoiceLines($validated['lines']);
                $lines = $result['lines'];
                $totals = $result['totals'];

                // Delete existing lines and create new ones
                $invoice->lines()->delete();

                foreach ($lines as $index => $lineData) {
                    InvoiceLine::create([
                        'invoice_id' => $invoice->id,
                        'description' => $lineData['description'],
                        'quantity' => $lineData['quantity'],
                        'unit' => $lineData['unit'] ?? 'unit',
                        'unit_price' => $lineData['unit_price'],
                        'vat_rate' => $lineData['vat_rate'],
                        'total_ht' => $lineData['total_ht'],
                        'total_vat' => $lineData['total_ttc'] - $lineData['total_ht'],
                        'total_ttc' => $lineData['total_ttc'],
                        'sort_order' => $index,
                    ]);
                }

                // Update invoice totals
                $updates['total_ht'] = $totals['total_ht'];
                $updates['total_vat'] = $totals['total_vat'];
                $updates['total_ttc'] = $totals['total_ttc'];
            }

            $invoice->update($updates);

            DB::commit();

            $this->auditLog->log(
                'update',
                'invoice',
                $invoice->id,
                [
                    'old' => array_intersect_key($oldValues, $updates),
                    'new' => $updates,
                    'lines_updated' => isset($validated['lines']),
                ]
            );

            return [
                'success' => true,
                'message' => 'Invoice updated successfully',
                'id' => $invoice->id,
                'number' => $invoice->number,
                'total_ttc' => $invoice->total_ttc,
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Invoice not found',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to update invoice',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Mark an invoice as paid
     *
     * @param int $id Invoice ID
     * @param float|null $amount Amount paid (optional, defaults to total_ttc)
     * @return array
     */
    public function markPaid(int $id, ?float $amount = null): array
    {
        try {
            $invoice = Invoice::findOrFail($id);

            if ($invoice->status === 'paid') {
                return [
                    'success' => false,
                    'message' => 'Invoice is already marked as paid',
                ];
            }

            $oldStatus = $invoice->status;
            $oldPaidAt = $invoice->paid_at;
            $oldAmountPaid = $invoice->amount_paid;

            $invoice->markAsPaid($amount);

            $this->auditLog->log(
                'mark_paid',
                'invoice',
                $invoice->id,
                [
                    'old' => [
                        'status' => $oldStatus,
                        'paid_at' => $oldPaidAt,
                        'amount_paid' => $oldAmountPaid,
                    ],
                    'new' => [
                        'status' => 'paid',
                        'paid_at' => $invoice->paid_at,
                        'amount_paid' => $invoice->amount_paid,
                    ],
                ]
            );

            return [
                'success' => true,
                'message' => "Invoice {$invoice->number} marked as paid",
                'id' => $invoice->id,
                'amount_paid' => (float) $invoice->amount_paid,
                'paid_at' => $invoice->paid_at,
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Invoice not found',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to mark invoice as paid',
                'error' => $e->getMessage(),
            ];
        }
    }
}
