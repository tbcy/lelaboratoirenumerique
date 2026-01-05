<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialConnection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'platform',
        'credentials',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'credentials' => 'array',
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = [
        'credentials',
    ];

    public static function getPlatformOptions(): array
    {
        return [
            'twitter' => __('resources.social_connection.platforms.twitter'),
            'linkedin' => __('resources.social_connection.platforms.linkedin'),
            'instagram' => __('resources.social_connection.platforms.instagram'),
            'facebook' => __('resources.social_connection.platforms.facebook'),
        ];
    }

    public function getPlatformIconAttribute(): string
    {
        return match ($this->platform) {
            'twitter' => 'heroicon-o-chat-bubble-left-right',
            'linkedin' => 'heroicon-o-briefcase',
            'instagram' => 'heroicon-o-camera',
            'facebook' => 'heroicon-o-user-group',
            default => 'heroicon-o-globe-alt',
        };
    }

    public function getDisplayNameAttribute(): string
    {
        $platformName = self::getPlatformOptions()[$this->platform] ?? ucfirst($this->platform);
        return "{$this->name} ({$platformName})";
    }

    public function updateLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
