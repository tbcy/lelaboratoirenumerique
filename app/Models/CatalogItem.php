<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatalogItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'catalog_category_id',
        'name',
        'description',
        'unit_price',
        'unit',
        'vat_rate',
        'default_quantity',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'default_quantity' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CatalogCategory::class, 'catalog_category_id');
    }

    public function getTotalPriceAttribute(): float
    {
        return (float) $this->unit_price * (float) $this->default_quantity;
    }

    public static function getUnitOptions(): array
    {
        return [
            'hour' => __('resources.catalog_item.units.hour'),
            'day' => __('resources.catalog_item.units.day'),
            'unit' => __('resources.catalog_item.units.unit'),
            'fixed' => __('resources.catalog_item.units.fixed'),
            'line' => __('resources.catalog_item.units.line'),
        ];
    }
}
