<?php

namespace App\Filament\Kanban;

use App\Filament\Kanban\Concerns\HasEditRecordModal;
use App\Filament\Kanban\Concerns\HasStatusChange;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnitEnum;

abstract class KanbanBoard extends Page
{
    use HasEditRecordModal;
    use HasStatusChange;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-view-columns';

    protected string $view = 'filament.kanban.kanban-board';

    protected static string $headerView = 'filament.kanban.kanban-header';

    protected static string $recordView = 'filament.kanban.kanban-record';

    protected static string $statusView = 'filament.kanban.kanban-status';

    protected static string $scriptsView = 'filament.kanban.kanban-scripts';

    protected static string $model;

    protected static string $statusEnum;

    protected static string $recordTitleAttribute = 'title';

    public function mount(): void
    {
        $this->mountHasEditRecordModal();
    }

    protected function statuses(): Collection
    {
        return static::$statusEnum::statuses();
    }

    protected function records(): Collection
    {
        return $this->getEloquentQuery()
            ->when(method_exists(static::$model, 'scopeOrdered'), fn ($query) => $query->ordered())
            ->get();
    }

    protected function getViewData(): array
    {
        $records = $this->records();
        $statuses = $this->statuses()
            ->map(function ($status) use ($records) {
                $status['records'] = $this->filterRecordsByStatus($records, $status);

                return $status;
            });

        return [
            'statuses' => $statuses,
            'statusView' => static::$statusView,
            'headerView' => static::$headerView,
            'recordView' => static::$recordView,
            'scriptsView' => static::$scriptsView,
            'disableEditModal' => $this->disableEditModal ?? false,
        ];
    }

    protected function filterRecordsByStatus(Collection $records, array $status): array
    {
        $statusIsCastToEnum = $records->first()?->getAttribute(static::$recordStatusAttribute) instanceof UnitEnum;

        $filter = $statusIsCastToEnum
            ? static::$statusEnum::from($status['id'])
            : $status['id'];

        return $records->where(static::$recordStatusAttribute, $filter)->all();
    }

    protected function getEloquentQuery(): Builder
    {
        return static::$model::query();
    }
}
