<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Note extends Model implements Sortable, HasMedia
{
    use SoftDeletes, SortableTrait, InteractsWithMedia;

    public $sortable = [
        'order_column_name' => 'sort_order',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'parent_id',
        'name',
        'datetime',
        'short_summary',
        'long_summary',
        'notes',
        'transcription',
        'sort_order',
    ];

    protected $casts = [
        'datetime' => 'datetime',
        'sort_order' => 'integer',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Note::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Note::class, 'parent_id')->orderBy('sort_order');
    }

    public function stakeholders(): BelongsToMany
    {
        return $this->belongsToMany(Stakeholder::class)->withTimestamps();
    }

    public function scopes(): BelongsToMany
    {
        return $this->belongsToMany(NoteScope::class)->withTimestamps();
    }

    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;
        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }
        return $depth;
    }

    public function getBreadcrumbAttribute(): string
    {
        $parts = [$this->name];
        $parent = $this->parent;
        while ($parent) {
            array_unshift($parts, $parent->name);
            $parent = $parent->parent;
        }
        return implode(' / ', $parts);
    }
}
