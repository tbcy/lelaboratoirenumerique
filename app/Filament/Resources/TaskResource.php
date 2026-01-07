<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use App\Models\Project;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use App\Filament\Traits\StandardTableConfig;

class TaskResource extends Resource
{
    use StandardTableConfig;

    protected static ?string $model = Task::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.projects');
    }

    public static function getModelLabel(): string
    {
        return __('resources.task.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.task.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->components([
                        Section::make(__('resources.task.sections.information'))
                            ->components([
                                Forms\Components\TextInput::make('title')
                                    ->label(__('resources.task.title'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\RichEditor::make('description')
                                    ->label(__('resources.task.description'))
                                    ->toolbarButtons(self::standardToolbar())
                                    ->columnSpanFull(),

                                Grid::make(2)
                                    ->components([
                                        Forms\Components\Select::make('status')
                                            ->label(__('resources.task.status'))
                                            ->options(Task::getStatusOptions())
                                            ->default('todo')
                                            ->required()
                                            ->searchable()
                                            ->preload(),

                                        Forms\Components\Select::make('priority')
                                            ->label(__('resources.task.priority'))
                                            ->options(Task::getPriorityOptions())
                                            ->default('medium')
                                            ->required()
                                            ->searchable()
                                            ->preload(),
                                    ]),

                                Grid::make(2)
                                    ->components([
                                        Forms\Components\DatePicker::make('due_date')
                                            ->label(__('resources.task.due_date')),

                                        Forms\Components\TextInput::make('estimated_minutes')
                                            ->label(__('resources.task.estimated_minutes'))
                                            ->numeric()
                                            ->minValue(0)
                                            ->suffix('min'),
                                    ]),
                            ]),

                        Section::make(__('resources.task.sections.time_tracking'))
                            ->components([
                                Forms\Components\Placeholder::make('logged_time_display')
                                    ->label(__('resources.task.time_spent'))
                                    ->content(function (?Task $record): string {
                                        if (!$record) {
                                            return __('resources.task.placeholders.no_time_entries');
                                        }

                                        $totalSeconds = $record->getTotalLoggedSeconds();
                                        $totalMinutes = round($totalSeconds / 60);
                                        $totalHours = $record->getTotalLoggedHours();

                                        $entriesCount = $record->timeEntries()
                                            ->whereNotNull('stopped_at')
                                            ->count();

                                        if ($entriesCount === 0) {
                                            return __('resources.task.placeholders.no_time_entries');
                                        }

                                        return __('resources.task.placeholders.time_display', [
                                            'minutes' => $totalMinutes,
                                            'hours' => $totalHours,
                                            'count' => $entriesCount,
                                        ]);
                                    })
                                    ->helperText(__('resources.task.helpers.auto_calculated')),

                                Forms\Components\Placeholder::make('progress')
                                    ->label(__('resources.task.progress'))
                                    ->content(function (?Task $record): string {
                                        if (!$record || !$record->estimated_minutes) {
                                            return __('resources.task.placeholders.na');
                                        }

                                        $totalMinutes = round($record->getTotalLoggedSeconds() / 60);
                                        $percent = min(100, round(($totalMinutes / $record->estimated_minutes) * 100));
                                        return __('resources.task.placeholders.progress_display', [
                                            'percent' => $percent,
                                            'logged' => $totalMinutes,
                                            'estimated' => $record->estimated_minutes,
                                        ]);
                                    }),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->collapsed(fn (?Task $record): bool => $record === null),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->components([
                        Section::make(__('resources.task.sections.associations'))
                            ->components([
                                Forms\Components\Select::make('client_id')
                                    ->label(__('resources.task.client'))
                                    ->relationship('client', 'company_name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $set('project_id', null);
                                    }),

                                Forms\Components\Select::make('project_id')
                                    ->label(__('resources.task.project'))
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
                                        !$get('client_id')
                                            ? __('resources.task.helpers.select_client_first')
                                            : null
                                    )
                                    ->createOptionForm([
                                        Forms\Components\Hidden::make('client_id')
                                            ->default(fn (Get $get) => $get('../../client_id')),
                                        Forms\Components\TextInput::make('name')
                                            ->label(__('resources.task.project_name'))
                                            ->required(),
                                    ]),

                                Forms\Components\Select::make('catalog_item_id')
                                    ->label(__('resources.task.billing_item'))
                                    ->relationship('catalogItem', 'name', fn (Builder $query) =>
                                        $query->where('unit', 'hour')->where('is_active', true)
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->helperText(__('resources.task.helpers.required_for_timer'))
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label(__('resources.task.form_labels.name'))
                                            ->required(),
                                        Forms\Components\TextInput::make('unit_price')
                                            ->label(__('resources.task.hourly_rate'))
                                            ->numeric()
                                            ->required()
                                            ->prefix('€'),
                                        Forms\Components\Hidden::make('unit')->default('hour'),
                                    ]),

                                Forms\Components\Select::make('parent_id')
                                    ->label(__('resources.task.parent_task'))
                                    ->relationship('parent', 'title', fn (Builder $query, ?Task $record) =>
                                        $query->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                    )
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\Select::make('stakeholders')
                                    ->label(__('resources.task.stakeholders'))
                                    ->relationship('stakeholders', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label(__('resources.stakeholder.name'))
                                            ->required(),
                                        Forms\Components\TextInput::make('email')
                                            ->label(__('resources.stakeholder.email'))
                                            ->email(),
                                        Forms\Components\TextInput::make('company')
                                            ->label(__('resources.stakeholder.company')),
                                    ]),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label(__('resources.task.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('resources.task.status'))
                    ->badge()
                    ->color(fn (string $state): string => self::getStatusColor($state))
                    ->formatStateUsing(fn (string $state): string => Task::getStatusOptions()[$state] ?? $state),

                Tables\Columns\TextColumn::make('priority')
                    ->label(__('resources.task.priority'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'info',
                        'high' => 'warning',
                        'urgent' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => Task::getPriorityOptions()[$state] ?? $state),

                Tables\Columns\TextColumn::make('project.name')
                    ->label(__('resources.task.project'))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('client.display_name')
                    ->label(__('resources.task.client'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('due_date')
                    ->label(__('resources.task.due'))
                    ->date(self::DATE_FORMAT)
                    ->sortable()
                    ->color(fn (Task $record): string => $record->is_overdue ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('estimated_minutes')
                    ->label(__('resources.task.estimated_short'))
                    ->formatStateUsing(fn (?int $state): string => $state ? "{$state} min" : '-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('logged_hours')
                    ->label(__('resources.task.time_logged_short'))
                    ->getStateUsing(fn (Task $record): string =>
                        round($record->getTotalLoggedSeconds() / 60) . " min"
                    )
                    ->sortable(query: fn (Builder $query, string $direction) =>
                        $query->withCount([
                            'timeEntries as total_seconds' => fn ($q) =>
                                $q->selectRaw('COALESCE(SUM(duration_seconds), 0)')->whereNotNull('stopped_at')
                        ])->orderBy('total_seconds', $direction)
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('resources.task.created_at'))
                    ->date(self::DATE_FORMAT)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('resources.task.status'))
                    ->options(Task::getStatusOptions()),

                Tables\Filters\SelectFilter::make('priority')
                    ->label(__('resources.task.priority'))
                    ->options(Task::getPriorityOptions()),

                Tables\Filters\SelectFilter::make('project_id')
                    ->label(__('resources.task.project'))
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('client_id')
                    ->label(__('resources.task.client'))
                    ->relationship('client', 'company_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('overdue')
                    ->label(__('resources.task.filters.overdue'))
                    ->query(fn (Builder $query): Builder => $query
                        ->where('status', '!=', 'done')
                        ->whereNotNull('due_date')
                        ->where('due_date', '<', now())
                    ),

                Tables\Filters\Filter::make('no_project')
                    ->label(__('resources.task.filters.no_project'))
                    ->query(fn (Builder $query): Builder => $query->whereNull('project_id')),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),

                    Actions\Action::make('logTime')
                        ->label(__('resources.task.actions.log_time'))
                        ->icon('heroicon-o-clock')
                        ->color('info')
                        ->form([
                            Forms\Components\TextInput::make('minutes')
                                ->label(__('resources.task.form_labels.minutes_to_add'))
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->default(30),

                            Forms\Components\Textarea::make('notes')
                                ->label(__('resources.task.form_labels.notes_optional'))
                                ->rows(2),
                        ])
                        ->action(function (Task $record, array $data) {
                            // Créer un TimeEntry au lieu d'incrémenter logged_minutes
                            $record->timeEntries()->create([
                                'user_id' => auth()->id(),
                                'started_at' => now(),
                                'stopped_at' => now()->addMinutes($data['minutes']),
                                'duration_seconds' => $data['minutes'] * 60,
                                'notes' => $data['notes'] ?? 'Ajout manuel de temps',
                            ]);

                            Notification::make()
                                ->title(__('resources.task.notifications.time_added'))
                                ->body(__('resources.task.notifications.time_added_body', ['minutes' => $data['minutes']]))
                                ->success()
                                ->send();
                        }),

                    Actions\Action::make('startTimer')
                        ->label(__('resources.task.actions.start_timer'))
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->visible(fn (Task $record): bool =>
                            !$record->getActiveTimer() && $record->catalog_item_id
                        )
                        ->action(function (Task $record) {
                            $record->startTimer();
                            $this->dispatch('timer-started');
                            Notification::make()
                                ->title(__('resources.task.notifications.timer_started'))
                                ->success()
                                ->send();
                        }),

                    Actions\Action::make('stopTimer')
                        ->label(__('resources.task.actions.stop_timer'))
                        ->icon('heroicon-o-stop')
                        ->color('danger')
                        ->visible(fn (Task $record): bool => (bool) $record->getActiveTimer())
                        ->form([
                            Forms\Components\Textarea::make('notes')
                                ->label(__('resources.task.form_labels.notes_optional'))
                                ->rows(3),
                        ])
                        ->action(function (Task $record, array $data) {
                            $record->stopTimer(notes: $data['notes'] ?? null);
                            $this->dispatch('timer-stopped');
                            Notification::make()
                                ->title(__('resources.task.notifications.timer_stopped'))
                                ->success()
                                ->send();
                        }),

                    Actions\Action::make('markAsDone')
                        ->label(__('resources.task.actions.mark_as_done'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Task $record): bool => $record->status !== 'done')
                        ->action(function (Task $record) {
                            $record->update(['status' => 'done']);
                            Notification::make()
                                ->title(__('resources.task.notifications.task_done'))
                                ->success()
                                ->send();
                        }),

                    Actions\Action::make('duplicate')
                        ->label(__('resources.task.actions.duplicate'))
                        ->icon('heroicon-o-document-duplicate')
                        ->action(function (Task $record) {
                            $newTask = $record->replicate();
                            $newTask->status = 'todo';
                            // logged_minutes supprimé - pas besoin de le définir
                            // TimeEntries ne sont pas dupliquées (par défaut)
                            $newTask->save();

                            Notification::make()
                                ->title(__('resources.task.notifications.duplicated'))
                                ->body(__('resources.task.notifications.duplicated_body'))
                                ->success()
                                ->send();
                        }),

                    Actions\DeleteAction::make(),
                    Actions\RestoreAction::make(),
                    Actions\ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),

                    Actions\BulkAction::make('markAsDone')
                        ->label(__('resources.task.actions.mark_as_done_plural'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->update(['status' => 'done']));
                            Notification::make()
                                ->title(__('resources.task.notifications.tasks_updated'))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Actions\BulkAction::make('changeStatus')
                        ->label(__('resources.task.actions.change_status'))
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label(__('resources.task.form_labels.new_status'))
                                ->options(Task::getStatusOptions())
                                ->required()
                                ->searchable()
                                ->preload(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(fn ($record) => $record->update(['status' => $data['status']]));
                            Notification::make()
                                ->title(__('resources.task.notifications.tasks_updated'))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->reorderable('sort_order');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TimeEntriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', '!=', 'done')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $overdueCount = static::getModel()::where('status', '!=', 'done')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();

        return $overdueCount > 0 ? 'danger' : 'warning';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description', 'project.name', 'client.company_name'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            __('resources.task.project') => $record->project?->name ?? '-',
            __('resources.task.status') => Task::getStatusOptions()[$record->status] ?? $record->status,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['project', 'client']);
    }
}
