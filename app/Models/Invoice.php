<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'number',
        'client_id',
        'quote_id',
        'project_id',
        'subject',
        'introduction',
        'conclusion',
        'status',
        'issue_date',
        'due_date',
        'total_ht',
        'total_vat',
        'total_ttc',
        'amount_paid',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'date',
        'total_ht' => 'decimal:2',
        'total_vat' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('sort_order');
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }

    public static function getStatusOptions(): array
    {
        return [
            'draft' => __('enums.invoice_status.draft'),
            'sent' => __('enums.invoice_status.sent'),
            'paid' => __('enums.invoice_status.paid'),
            'partial' => __('enums.invoice_status.partially_paid'),
            'overdue' => __('enums.invoice_status.overdue'),
            'cancelled' => __('enums.invoice_status.cancelled'),
        ];
    }

    public function calculateTotals(): void
    {
        $totalHt = $this->lines()->sum('total_ht');
        $totalVat = $this->lines()->sum('total_vat');

        $this->update([
            'total_ht' => $totalHt,
            'total_vat' => $totalVat,
            'total_ttc' => $totalHt + $totalVat,
        ]);
    }

    public function markAsPaid(?float $amount = null): void
    {
        $this->update([
            'status' => 'paid',
            'amount_paid' => $amount ?? $this->total_ttc,
            'paid_at' => now(),
        ]);
    }

    public function getAmountDueAttribute(): float
    {
        return max(0, $this->total_ttc - $this->amount_paid);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid'
            && $this->status !== 'cancelled'
            && $this->due_date
            && $this->due_date->isPast();
    }
}
