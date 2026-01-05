<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneratedImage extends Model
{
    protected $fillable = [
        'prompt',
        'content_source',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'social_post_id',
        'api_response',
    ];

    protected $casts = [
        'api_response' => 'array',
        'file_size' => 'integer',
    ];

    public function socialPost(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class);
    }

    /**
     * Get the full storage path for the image.
     */
    public function getFullPathAttribute(): string
    {
        return storage_path('app/' . $this->file_path);
    }

    /**
     * Get the URL for serving the image.
     */
    public function getUrlAttribute(): string
    {
        return route('generated-images.show', $this);
    }
}
