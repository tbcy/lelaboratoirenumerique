<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use App\Models\Quote;
use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActivityStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Tasks in progress
        $tasksInProgress = Task::whereIn('status', ['todo', 'in_progress'])->count();
        $overdueTasks = Task::whereNotIn('status', ['done', 'cancelled'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();

        // Published posts
        $publishedCount = Post::where('status', 'published')->count();
        $draftCount = Post::where('status', 'draft')->count();

        // Pending quotes
        $pendingQuotes = Quote::where('status', 'sent')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_ttc), 0) as total')
            ->first();
        $quotesCount = $pendingQuotes->count ?? 0;
        $quotesTotal = $pendingQuotes->total ?? 0;

        return [
            Stat::make(
                __('widgets.dashboard.tasks_in_progress'),
                $tasksInProgress . ($overdueTasks > 0 ? ' (' . $overdueTasks . ' ' . __('widgets.dashboard.overdue') . ')' : '')
            )
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color($overdueTasks > 0 ? 'warning' : ($tasksInProgress > 0 ? 'primary' : 'gray')),

            Stat::make(
                __('widgets.dashboard.published_posts'),
                $publishedCount . ' ' . trans_choice('widgets.dashboard.post_count', $publishedCount)
            )
                ->description($draftCount > 0 ? $draftCount . ' ' . __('widgets.dashboard.drafts') : __('widgets.dashboard.no_drafts'))
                ->descriptionIcon('heroicon-m-document-text')
                ->color($publishedCount > 0 ? 'success' : 'gray'),

            Stat::make(
                __('widgets.dashboard.pending_quotes'),
                $quotesCount . ' ' . trans_choice('widgets.dashboard.quote_count', $quotesCount)
            )
                ->description(number_format($quotesTotal, 2, ',', ' ') . ' €')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($quotesCount > 0 ? 'info' : 'gray'),
        ];
    }
}
