<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'company_name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'address',
        'address_2',
        'postal_code',
        'city',
        'country',
        'siret',
        'vat_number',
        'status',
        'notes',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->type === 'company') {
            return $this->company_name ?? '';
        }

        return trim($this->first_name . ' ' . $this->last_name);
    }

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
}
