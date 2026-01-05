<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinanceStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Total revenue from paid invoices
        $totalRevenue = Invoice::where('status', 'paid')->sum('total_ttc');

        // Unpaid invoices (not paid, not cancelled)
        $unpaid = Invoice::whereNotIn('status', ['paid', 'cancelled'])
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_ttc - amount_paid), 0) as total')
            ->first();
        $unpaidCount = $unpaid->count ?? 0;
        $unpaidTotal = $unpaid->total ?? 0;

        // Overdue invoices (past due date, not paid, not cancelled)
        $overdue = Invoice::whereNotIn('status', ['paid', 'cancelled'])
            ->where('due_date', '<', now())
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total_ttc - amount_paid), 0) as total')
            ->first();
        $overdueCount = $overdue->count ?? 0;
        $overdueTotal = $overdue->total ?? 0;

        return [
            Stat::make(
                __('widgets.dashboard.total_revenue'),
                number_format($totalRevenue, 2, ',', ' ') . ' €'
            )
                ->description(__('widgets.dashboard.since_beginning'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make(
                __('widgets.dashboard.unpaid_invoices'),
                $unpaidCount . ' ' . trans_choice('widgets.dashboard.invoice_count', $unpaidCount)
            )
                ->description(number_format($unpaidTotal, 2, ',', ' ') . ' €')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($unpaidCount > 0 ? 'warning' : 'success'),

            Stat::make(
                __('widgets.dashboard.overdue_invoices'),
                $overdueCount . ' ' . trans_choice('widgets.dashboard.invoice_count', $overdueCount)
            )
                ->description(number_format($overdueTotal, 2, ',', ' ') . ' €')
                ->descriptionIcon('heroicon-m-clock')
                ->color($overdueCount > 0 ? 'danger' : 'success'),
        ];
    }
}
