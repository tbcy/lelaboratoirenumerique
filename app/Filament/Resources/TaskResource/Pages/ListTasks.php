<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Pages\TasksKanbanBoard;
use App\Filament\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('kanbanView')
                ->label('Vue Kanban')
                ->icon('heroicon-o-view-columns')
                ->url(TasksKanbanBoard::getUrl()),

            Actions\CreateAction::make(),
        ];
    }
}
