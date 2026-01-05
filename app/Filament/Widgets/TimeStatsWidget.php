<?php

namespace App\Filament\Widgets;

use App\Models\TimeEntry;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TimeStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Today
        $todaySeconds = TimeEntry::whereDate('started_at', today())
            ->sum('duration_seconds');

        // This week
        $weekSeconds = TimeEntry::whereBetween('started_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ])->sum('duration_seconds');

        // This month
        $monthSeconds = TimeEntry::whereMonth('started_at', now()->month)
            ->whereYear('started_at', now()->year)
            ->sum('duration_seconds');

        return [
            Stat::make(
                __('widgets.dashboard.today'),
                $this->formatDuration($todaySeconds)
            )
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),

            Stat::make(
                __('widgets.dashboard.this_week'),
                $this->formatDuration($weekSeconds)
            )
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),

            Stat::make(
                __('widgets.dashboard.this_month'),
                $this->formatDuration($monthSeconds)
            )
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
        ];
    }

    private function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return sprintf('%dh %02dmin', $hours, $minutes);
    }
}
