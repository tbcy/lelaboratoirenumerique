<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'content',
        'images',
        'connection_ids',
        'status',
        'scheduled_at',
        'published_at',
        'error_message',
    ];

    protected $casts = [
        'images' => 'array',
        'connection_ids' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public static function getStatusOptions(): array
    {
        return [
            'draft' => __('enums.social_post_status.draft'),
            'approved' => __('enums.social_post_status.approved'),
            'scheduled' => __('enums.social_post_status.scheduled'),
            'published' => __('enums.social_post_status.published'),
            'failed' => __('enums.social_post_status.failed'),
            'rejected' => __('enums.social_post_status.rejected'),
        ];
    }

    public function connections()
    {
        return SocialConnection::whereIn('id', $this->connection_ids ?? [])->get();
    }

    public function socialConnections()
    {
        return SocialConnection::whereIn('id', $this->connection_ids ?? [])->get();
    }

    public function approve(): void
    {
        $this->update(['status' => 'approved']);
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }

    public function markAsScheduled(): void
    {
        $this->update([
            'status' => 'scheduled',
        ]);
    }

    public function markAsPublished(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}
