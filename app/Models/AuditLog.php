<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'action',
        'resource_type',
        'resource_id',
        'changes',
        'api_key_identifier',
    ];

    protected $casts = [
        'changes' => 'array',
    ];
}
