<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Quote extends Model implements HasMedia
{
    use SoftDeletes;
    use InteractsWithMedia;

    protected $fillable = [
        'number',
        'client_id',
        'project_id',
        'subject',
        'introduction',
        'conclusion',
        'status',
        'issue_date',
        'validity_date',
        'total_ht',
        'total_vat',
        'total_ttc',
        'notes',
        'accepted_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'validity_date' => 'date',
        'accepted_at' => 'date',
        'total_ht' => 'decimal:2',
        'total_vat' => 'decimal:2',
        'total_ttc' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(QuoteLine::class)->orderBy('sort_order');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }

    public static function getStatusOptions(): array
    {
        return [
            'draft' => __('enums.quote_status.draft'),
            'sent' => __('enums.quote_status.sent'),
            'accepted' => __('enums.quote_status.accepted'),
            'refused' => __('enums.quote_status.rejected'),
            'expired' => __('enums.quote_status.expired'),
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

    public function convertToInvoice(): Invoice
    {
        $company = Company::first();

        $invoice = Invoice::create([
            'number' => $company->generateInvoiceNumber(),
            'client_id' => $this->client_id,
            'quote_id' => $this->id,
            'project_id' => $this->project_id,
            'subject' => $this->subject,
            'introduction' => $this->introduction,
            'conclusion' => $this->conclusion,
            'status' => 'draft',
            'issue_date' => now(),
            'due_date' => now()->addDays($company->default_payment_delay),
            'notes' => $this->notes,
        ]);

        foreach ($this->lines as $line) {
            $invoice->lines()->create([
                'catalog_item_id' => $line->catalog_item_id,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'unit' => $line->unit,
                'unit_price' => $line->unit_price,
                'vat_rate' => $line->vat_rate,
                'sort_order' => $line->sort_order,
            ]);
        }

        $this->update(['status' => 'accepted', 'accepted_at' => now()]);

        return $invoice;
    }
}
