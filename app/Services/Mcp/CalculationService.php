<?php

namespace App\Services\Mcp;

/**
 * Service for calculating invoice and quote totals
 *
 * Ensures consistent calculation logic across the application
 * and prevents client-side manipulation of calculated fields
 */
class CalculationService
{
    /**
     * Calculate line item totals
     *
     * @param array $line Line item with quantity, unit_price, vat_rate
     * @return array Line with calculated total_ht, total_vat, and total_ttc
     */
    public function calculateLineTotal(array $line): array
    {
        $quantity = (float) ($line['quantity'] ?? 0);
        $unitPrice = (float) ($line['unit_price'] ?? 0);
        $vatRate = (float) ($line['vat_rate'] ?? 20);

        $totalHT = round($quantity * $unitPrice, 2);
        $totalVAT = round($totalHT * ($vatRate / 100), 2);
        $totalTTC = round($totalHT + $totalVAT, 2);

        return array_merge($line, [
            'total_ht' => $totalHT,
            'total_vat' => $totalVAT,
            'total_ttc' => $totalTTC,
        ]);
    }

    /**
     * Calculate invoice/quote totals from lines
     *
     * @param array $lines Array of line items
     * @return array Calculated totals: total_ht, total_vat, total_ttc
     */
    public function calculateInvoiceTotals(array $lines): array
    {
        $totalHT = 0;
        $totalTTC = 0;

        foreach ($lines as $line) {
            $totalHT += (float) ($line['total_ht'] ?? 0);
            $totalTTC += (float) ($line['total_ttc'] ?? 0);
        }

        $totalHT = round($totalHT, 2);
        $totalTTC = round($totalTTC, 2);
        $totalVAT = round($totalTTC - $totalHT, 2);

        return [
            'total_ht' => $totalHT,
            'total_vat' => $totalVAT,
            'total_ttc' => $totalTTC,
        ];
    }

    /**
     * Process invoice/quote lines and calculate all totals
     *
     * @param array $lines Array of line items
     * @return array Processed lines with totals and invoice totals
     */
    public function processInvoiceLines(array $lines): array
    {
        $processedLines = [];

        foreach ($lines as $line) {
            $processedLines[] = $this->calculateLineTotal($line);
        }

        $totals = $this->calculateInvoiceTotals($processedLines);

        return [
            'lines' => $processedLines,
            'totals' => $totals,
        ];
    }

    /**
     * Process quote lines and calculate all totals (alias for processInvoiceLines)
     *
     * @param array $lines Array of line items
     * @return array Processed lines with totals and quote totals
     */
    public function processQuoteLines(array $lines): array
    {
        return $this->processInvoiceLines($lines);
    }

    /**
     * Calculate amount paid from payment transactions
     *
     * @param \Illuminate\Database\Eloquent\Collection $payments
     * @return float Total amount paid
     */
    public function calculateAmountPaid($payments): float
    {
        return round($payments->sum('amount'), 2);
    }

    /**
     * Calculate remaining amount to pay
     *
     * @param float $totalTTC Total invoice amount
     * @param float $amountPaid Amount already paid
     * @return float Remaining balance
     */
    public function calculateBalance(float $totalTTC, float $amountPaid): float
    {
        return round($totalTTC - $amountPaid, 2);
    }

    /**
     * Validate payment amount
     *
     * @param float $totalTTC Total invoice amount
     * @param float $amountPaid Already paid
     * @param float $paymentAmount New payment amount
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validatePaymentAmount(float $totalTTC, float $amountPaid, float $paymentAmount): array
    {
        $balance = $this->calculateBalance($totalTTC, $amountPaid);

        if ($paymentAmount <= 0) {
            return [
                'valid' => false,
                'message' => 'Payment amount must be greater than zero.',
            ];
        }

        if ($paymentAmount > $balance) {
            return [
                'valid' => false,
                'message' => sprintf(
                    'Payment amount (%.2f) exceeds remaining balance (%.2f).',
                    $paymentAmount,
                    $balance
                ),
            ];
        }

        return ['valid' => true, 'message' => 'Valid payment amount.'];
    }
}
