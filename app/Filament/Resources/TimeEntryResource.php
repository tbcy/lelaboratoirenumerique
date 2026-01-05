<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TimeEntryResource\Pages;
use App\Models\TimeEntry;
use App\Models\Project;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TimeEntryResource extends Resource
{
    protected static ?string $model = TimeEntry::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.management');
    }

    public static function getModelLabel(): string
    {
        return __('resources.time_entry.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.time_entry.plural');
    }

    /**
     * Determine if the resource should appear in navigation
     */
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    /**
     * Scoping: users only see their own entries
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.time_entry.sections.details'))
                    ->components([
                        Forms\Components\Select::make('task_id')
                            ->label(__('resources.time_entry.task'))
                            ->relationship('task', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('user_id')
                            ->label(__('resources.time_entry.user'))
                            ->relationship('user', 'name')
                            ->default(auth()->id())
                            ->required()
                            ->disabled(fn () => !auth()->user()->isAdmin()),
                    ])
                    ->columns(2),

                Section::make(__('resources.time_entry.sections.time'))
                    ->components([
                        Forms\Components\DateTimePicker::make('started_at')
                            ->label(__('resources.time_entry.started_at'))
                            ->required()
                            ->default(now())
                            ->seconds(false),

                        Forms\Components\DateTimePicker::make('stopped_at')
                            ->label(__('resources.time_entry.stopped_at'))
                            ->seconds(false)
                            ->after('started_at')
                            ->helperText(__('help.time_entry.end_time_empty')),

                        Forms\Components\Placeholder::make('duration_display')
                            ->label(__('resources.time_entry.duration_calculated'))
                            ->content(function ($get) {
                                $start = $get('started_at');
                                $stop = $get('stopped_at');
                                if (!$start) return __('resources.time_entry.placeholders.na');

                                $startTime = Carbon::parse($start);
                                $stopTime = $stop ? Carbon::parse($stop) : now();
                                $seconds = $startTime->diffInSeconds($stopTime);

                                return sprintf(
                                    '%02d:%02d (%0.2fh)',
                                    floor($seconds / 3600),
                                    floor(($seconds % 3600) / 60),
                                    $seconds / 3600
                                );
                            }),

                        Forms\Components\Textarea::make('notes')
                            ->label(__('resources.time_entry.notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('task.title')
                    ->label(__('resources.time_entry.task'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (TimeEntry $record) => $record->task?->project?->name)
                    ->wrap(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('resources.time_entry.user'))
                    ->sortable()
                    ->toggleable()
                    ->visible(fn () => auth()->user()->isAdmin()),

                Tables\Columns\TextColumn::make('started_at')
                    ->label(__('resources.time_entry.started_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stopped_at')
                    ->label(__('resources.time_entry.stopped_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder(__('resources.time_entry.placeholders.in_progress'))
                    ->color(fn ($state) => is_null($state) ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('formatted_duration')
                    ->label(__('resources.time_entry.duration'))
                    ->sortable(query: fn (Builder $query, string $direction) =>
                        $query->orderBy('duration_seconds', $direction)
                    ),

                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('resources.time_entry.status'))
                    ->getStateUsing(fn (TimeEntry $record) => $record->is_running ? __('resources.time_entry.statuses.running') : __('resources.time_entry.statuses.completed'))
                    ->colors([
                        'success' => fn ($state) => $state === __('resources.time_entry.statuses.running'),
                        'gray' => fn ($state) => $state === __('resources.time_entry.statuses.completed'),
                    ]),
            ])
            ->filters([
                // Filter by project
                Tables\Filters\SelectFilter::make('project')
                    ->label(__('resources.time_entry.project'))
                    ->query(fn (Builder $query, array $data) =>
                        $query->when($data['value'], fn (Builder $q) =>
                            $q->whereHas('task', fn ($tq) => $tq->where('project_id', $data['value']))
                        )
                    )
                    ->options(Project::pluck('name', 'id')),

                // Filter by status
                Tables\Filters\Filter::make('status')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label(__('resources.time_entry.status'))
                            ->options([
                                'running' => __('resources.time_entry.statuses.running'),
                                'completed' => __('resources.time_entry.statuses.completed'),
                            ]),
                    ])
                    ->query(fn (Builder $query, array $data) =>
                        $query->when(
                            $data['status'] === 'running',
                            fn (Builder $q) => $q->whereNull('stopped_at'),
                            fn (Builder $q) => $q->when(
                                $data['status'] === 'completed',
                                fn (Builder $q2) => $q2->whereNotNull('stopped_at')
                            )
                        )
                    ),

                // Filter by period
                Tables\Filters\Filter::make('started_at')
                    ->label(__('resources.time_entry.period'))
                    ->form([
                        Forms\Components\DatePicker::make('started_from')
                            ->label(__('resources.time_entry.from')),
                        Forms\Components\DatePicker::make('started_until')
                            ->label(__('resources.time_entry.to')),
                    ])
                    ->query(fn (Builder $query, array $data) =>
                        $query
                            ->when($data['started_from'], fn (Builder $q, $date) =>
                                $q->whereDate('started_at', '>=', $date)
                            )
                            ->when($data['started_until'], fn (Builder $q, $date) =>
                                $q->whereDate('started_at', '<=', $date)
                            )
                    ),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),

                    Actions\EditAction::make()
                        ->visible(fn (TimeEntry $record) =>
                            auth()->user()->isAdmin() ||
                            ($record->user_id === auth()->id() && !$record->stopped_at)
                        ),

                    Actions\Action::make('stop')
                        ->label(__('resources.time_entry.actions.stop'))
                        ->icon('heroicon-o-stop')
                        ->color('danger')
                        ->visible(fn (TimeEntry $record) => $record->is_running && $record->user_id === auth()->id())
                        ->form([
                            Forms\Components\Textarea::make('notes')
                                ->label(__('resources.time_entry.notes')),
                        ])
                        ->action(function (TimeEntry $record, array $data) {
                            $record->stop($data['notes'] ?? null);
                        })
                        ->successNotificationTitle(__('resources.time_entry.actions.stop_notification')),

                    Actions\DeleteAction::make()
                        ->visible(fn (TimeEntry $record) =>
                            auth()->user()->isAdmin() || $record->user_id === auth()->id()
                        ),
                    Actions\RestoreAction::make()
                        ->visible(fn (TimeEntry $record) =>
                            auth()->user()->isAdmin() || $record->user_id === auth()->id()
                        ),
                    Actions\ForceDeleteAction::make()
                        ->visible(fn (TimeEntry $record) =>
                            auth()->user()->isAdmin() || $record->user_id === auth()->id()
                        ),
                ]),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->recordUrl(
                fn (TimeEntry $record): string => route('filament.admin.resources.time-entries.edit', ['record' => $record])
            )
            ->defaultSort('started_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTimeEntries::route('/'),
            'create' => Pages\CreateTimeEntry::route('/create'),
            'view' => Pages\ViewTimeEntry::route('/{record}'),
            'edit' => Pages\EditTimeEntry::route('/{record}/edit'),
        ];
    }
}
