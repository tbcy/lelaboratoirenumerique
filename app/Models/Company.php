<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'legal_form',
        'siret',
        'vat_number',
        'address',
        'address_2',
        'postal_code',
        'city',
        'country',
        'phone',
        'email',
        'website',
        'logo',
        'iban',
        'bic',
        'bank_name',
        'legal_mentions',
        'openai_api_key',
        'openai_chat_model',
        'summary_detail_level',
        'image_generation_prompt',
        'quote_prefix',
        'quote_counter',
        'invoice_prefix',
        'invoice_counter',
        'default_payment_delay',
        'default_vat_rate',
    ];

    protected $casts = [
        'default_vat_rate' => 'decimal:2',
        'quote_counter' => 'integer',
        'invoice_counter' => 'integer',
        'default_payment_delay' => 'integer',
    ];

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->address_2,
            $this->postal_code . ' ' . $this->city,
            $this->country,
        ]);

        return implode("\n", $parts);
    }

    public function generateQuoteNumber(): string
    {
        $number = $this->quote_prefix . date('Y') . '-' . str_pad($this->quote_counter, 4, '0', STR_PAD_LEFT);
        $this->increment('quote_counter');
        return $number;
    }

    public function generateInvoiceNumber(): string
    {
        $number = $this->invoice_prefix . date('Y') . '-' . str_pad($this->invoice_counter, 4, '0', STR_PAD_LEFT);
        $this->increment('invoice_counter');
        return $number;
    }
}
