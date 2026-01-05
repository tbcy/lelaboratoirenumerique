<?php

namespace App\Console\Commands;

use App\Models\QuoteLine;
use App\Models\InvoiceLine;
use App\Models\Quote;
use App\Models\Invoice;
use Illuminate\Console\Command;

class RecalculateLineTotals extends Command
{
    protected $signature = 'totals:recalculate';
    protected $description = 'Recalculate all totals for quote lines, invoice lines, quotes, and invoices';

    public function handle()
    {
        $this->info('Starting recalculation of all totals...');

        // Recalculate Quote Lines
        $this->info('Recalculating quote line totals...');
        $quoteLines = QuoteLine::all();
        $quoteLineCount = 0;
        foreach ($quoteLines as $line) {
            $line->calculateTotals();
            $line->saveQuietly(); // Save without triggering events
            $quoteLineCount++;
        }
        $this->info("✓ Recalculated {$quoteLineCount} quote lines");

        // Recalculate Invoice Lines
        $this->info('Recalculating invoice line totals...');
        $invoiceLines = InvoiceLine::all();
        $invoiceLineCount = 0;
        foreach ($invoiceLines as $line) {
            $line->calculateTotals();
            $line->saveQuietly(); // Save without triggering events
            $invoiceLineCount++;
        }
        $this->info("✓ Recalculated {$invoiceLineCount} invoice lines");

        // Recalculate Quotes
        $this->info('Recalculating quote totals...');
        $quotes = Quote::all();
        $quoteCount = 0;
        foreach ($quotes as $quote) {
            $quote->calculateTotals();
            $quoteCount++;
        }
        $this->info("✓ Recalculated {$quoteCount} quotes");

        // Recalculate Invoices
        $this->info('Recalculating invoice totals...');
        $invoices = Invoice::all();
        $invoiceCount = 0;
        foreach ($invoices as $invoice) {
            $invoice->calculateTotals();
            $invoiceCount++;
        }
        $this->info("✓ Recalculated {$invoiceCount} invoices");

        $this->newLine();
        $this->info('✅ All totals recalculated successfully!');
        $this->info("Summary: {$quoteLineCount} quote lines, {$invoiceLineCount} invoice lines, {$quoteCount} quotes, {$invoiceCount} invoices");

        return Command::SUCCESS;
    }
}
