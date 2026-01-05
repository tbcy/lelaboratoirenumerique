<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'loggable_type',
        'loggable_id',
        'action',
        'description',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function log(
        Model $model,
        string $action,
        string $description,
        ?array $properties = null
    ): self {
        return self::create([
            'user_id' => auth()->id(),
            'loggable_type' => get_class($model),
            'loggable_id' => $model->id,
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
        ]);
    }
}
