<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FinanceStatsWidget;
use App\Filament\Widgets\PendingQuotesWidget;
use App\Filament\Widgets\TasksWidget;
use App\Filament\Widgets\TimeStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    public function getWidgets(): array
    {
        return [
            FinanceStatsWidget::class,
            TimeStatsWidget::class,
            TasksWidget::class,
            PendingQuotesWidget::class,
        ];
    }

    public function getColumns(): array|int
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 3,
        ];
    }
}
