<?php

namespace App\Filament\Pages;

use App\Enums\TaskStatus;
use App\Filament\Kanban\KanbanBoard;
use App\Models\Task;
use App\Models\Project;
use Filament\Forms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Grid;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;

class TasksKanbanBoard extends KanbanBoard
{
    protected static string $model = Task::class;
    protected static string $statusEnum = TaskStatus::class;
    protected static string $recordTitleAttribute = 'title';
    protected static string $recordStatusAttribute = 'status';
    protected static ?string $recordSortOrderAttribute = 'sort_order';
    protected static string $recordView = 'tasks.kanban-record';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-view-columns';
    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('resources.kanban.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.projects');
    }

    protected function getEditModalTitle(): string
    {
        return __('resources.kanban.edit_modal_title');
    }

    protected function getEditModalSaveButtonLabel(): string
    {
        return __('resources.kanban.edit_modal_save');
    }

    protected function getEditModalCancelButtonLabel(): string
    {
        return __('resources.kanban.edit_modal_cancel');
    }

    protected string $editModalWidth = '2xl';

    protected function records(): Collection
    {
        return $this->getEloquentQuery()
            ->with(['project', 'client', 'subtasks'])
            ->whereNull('parent_id')
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    protected function getEditModalFormSchema(int|string|null $recordId): array
    {
        return [
            Forms\Components\TextInput::make('title')
                ->label(__('resources.kanban.fields.title'))
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Forms\Components\Textarea::make('description')
                ->label(__('resources.kanban.fields.description'))
                ->rows(3)
                ->columnSpanFull(),

            Grid::make(2)
                ->schema([
                    Forms\Components\Select::make('priority')
                        ->label(__('resources.kanban.fields.priority'))
                        ->options(Task::getPriorityOptions())
                        ->default('medium')
                        ->required(),

                    Forms\Components\DatePicker::make('due_date')
                        ->label(__('resources.kanban.fields.due_date')),
                ]),

            Grid::make(2)
                ->schema([
                    Forms\Components\TextInput::make('estimated_minutes')
                        ->label(__('resources.kanban.fields.estimated_minutes'))
                        ->numeric()
                        ->minValue(0)
                        ->suffix(__('resources.kanban.fields.estimated_suffix')),

                    Forms\Components\Placeholder::make('logged_time_display')
                        ->label(__('resources.kanban.fields.logged_time'))
                        ->content(function ($record): string {
                            if (!$record) {
                                return __('resources.kanban.placeholders.no_time');
                            }

                            $totalMinutes = round($record->getTotalLoggedSeconds() / 60);
                            $totalHours = $record->getTotalLoggedHours();

                            return __('resources.kanban.placeholders.time_display', [
                                'minutes' => $totalMinutes,
                                'hours' => $totalHours,
                            ]);
                        })
                        ->helperText(__('resources.kanban.fields.logged_time_helper')),
                ]),

            Forms\Components\Select::make('client_id')
                ->label(__('resources.kanban.fields.client'))
                ->relationship('client', 'company_name')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(function ($state, Set $set) {
                    $set('project_id', null);
                }),

            Forms\Components\Select::make('project_id')
                ->label(__('resources.kanban.fields.project'))
                ->relationship('project', 'name')
                ->searchable()
                ->preload()
                ->disabled(fn (Get $get): bool => !$get('client_id'))
                ->options(function (Get $get) {
                    $clientId = $get('client_id');

                    if (!$clientId) {
                        return Project::query()->pluck('name', 'id');
                    }

                    return Project::query()
                        ->where('client_id', $clientId)
                        ->pluck('name', 'id');
                })
                ->helperText(fn (Get $get): ?string =>
                    !$get('client_id') ? __('resources.kanban.helpers.select_client_first') : null
                ),

            Forms\Components\Select::make('stakeholders')
                ->label(__('resources.task.stakeholders'))
                ->relationship('stakeholders', 'name')
                ->multiple()
                ->searchable()
                ->preload(),
        ];
    }

    public function startTaskTimer(int $taskId): void
    {
        try {
            $task = Task::findOrFail($taskId);
            $task->startTimer();

            $this->dispatch('timer-started');

            Notification::make()
                ->title(__('resources.kanban.notifications.timer_started'))
                ->body(__('resources.kanban.notifications.timer_started_body', ['title' => $task->title]))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title(__('resources.kanban.notifications.error'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    #[On('timer-stopped')]
    public function refreshAfterTimerStopped(): void
    {
        $this->dispatch('$refresh');
    }
}
