<?php

namespace App\Filament\Traits;

/**
 * Standardized configuration for Filament resources.
 * Provides consistent date formats, status colors, and RichEditor toolbars.
 */
trait StandardTableConfig
{
    // Date formats
    public const DATE_FORMAT = 'd/m/Y';
    public const DATETIME_FORMAT = 'd/m/Y H:i';

    /**
     * Get standardized status badge color.
     *
     * Color mapping:
     * - gray: Initial states (draft, todo, prospect)
     * - info: Progress states (in_progress, sent, scheduled, pending)
     * - warning: Attention states (review, partial, on_hold, expired)
     * - success: Positive states (published, done, paid, accepted, approved, active)
     * - danger: Negative states (failed, refused, rejected, overdue, cancelled, inactive)
     * - primary: Completed states
     */
    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            // Initial states
            'draft', 'todo', 'prospect' => 'gray',

            // Progress states
            'in_progress', 'sent', 'scheduled', 'pending' => 'info',

            // Attention states
            'review', 'partial', 'on_hold', 'expired' => 'warning',

            // Positive states
            'active', 'published', 'done', 'paid', 'accepted', 'approved' => 'success',

            // Negative states
            'cancelled', 'refused', 'rejected', 'failed', 'overdue', 'inactive' => 'danger',

            // Completed states
            'completed' => 'primary',

            default => 'gray',
        };
    }

    /**
     * Minimal toolbar for short notes and comments.
     */
    public static function minimalToolbar(): array
    {
        return ['bold', 'italic', 'link'];
    }

    /**
     * Standard toolbar for descriptions and summaries.
     */
    public static function standardToolbar(): array
    {
        return ['bold', 'italic', 'bulletList', 'orderedList', 'link'];
    }

    /**
     * Full toolbar for rich editorial content.
     */
    public static function fullToolbar(): array
    {
        return [
            'h2',
            'h3',
            'bold',
            'italic',
            'bulletList',
            'orderedList',
            'blockquote',
            'codeBlock',
            'link',
            'attachFiles',
            'undo',
            'redo',
        ];
    }
}
