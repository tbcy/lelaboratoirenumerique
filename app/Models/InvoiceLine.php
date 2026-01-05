<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceLine extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'catalog_item_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'vat_rate',
        'total_ht',
        'total_vat',
        'total_ttc',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'total_ht' => 'decimal:2',
        'total_vat' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (InvoiceLine $line) {
            $line->calculateTotals();
        });

        static::saved(function (InvoiceLine $line) {
            $line->invoice->calculateTotals();
        });

        static::deleted(function (InvoiceLine $line) {
            $line->invoice->calculateTotals();
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class);
    }

    public function calculateTotals(): void
    {
        // Force numeric conversion to avoid casting issues
        $quantity = floatval($this->quantity ?? 0);
        $unitPrice = floatval($this->unit_price ?? 0);
        $vatRate = floatval($this->vat_rate ?? 0);

        // Calculate totals with proper rounding
        $totalHt = round($quantity * $unitPrice, 2);
        $totalVat = round($totalHt * ($vatRate / 100), 2);
        $totalTtc = round($totalHt + $totalVat, 2);

        // Explicitly set attributes to force Laravel to mark them as dirty
        $this->setAttribute('total_ht', $totalHt);
        $this->setAttribute('total_vat', $totalVat);
        $this->setAttribute('total_ttc', $totalTtc);
    }
}
