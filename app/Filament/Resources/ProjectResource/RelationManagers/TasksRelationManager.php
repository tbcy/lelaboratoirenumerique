<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'Tâches';

    protected static ?string $modelLabel = 'Tâche';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('title')
                    ->label('Titre')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\RichEditor::make('description')
                    ->label('Description')
                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList'])
                    ->columnSpanFull(),

                Grid::make(2)
                    ->components([
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options(Task::getStatusOptions())
                            ->default('todo')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('priority')
                            ->label('Priorité')
                            ->options(Task::getPriorityOptions())
                            ->default('medium')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ]),

                Grid::make(2)
                    ->components([
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Date d\'échéance'),

                        Forms\Components\TextInput::make('estimated_minutes')
                            ->label('Temps estimé (min)')
                            ->numeric()
                            ->minValue(0),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'secondary' => 'todo',
                        'info' => 'in_progress',
                        'warning' => 'review',
                        'success' => 'done',
                    ])
                    ->formatStateUsing(fn ($state): string => $state instanceof \App\Enums\TaskStatus ? $state->getLabel() : (Task::getStatusOptions()[$state] ?? $state)),

                Tables\Columns\BadgeColumn::make('priority')
                    ->label('Priorité')
                    ->colors([
                        'gray' => 'low',
                        'info' => 'medium',
                        'warning' => 'high',
                        'danger' => 'urgent',
                    ])
                    ->formatStateUsing(fn (string $state): string => Task::getPriorityOptions()[$state] ?? $state),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Échéance')
                    ->date('d/m/Y')
                    ->color(fn (Task $record): string => $record->is_overdue ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('logged_hours')
                    ->label('Temps passé')
                    ->getStateUsing(fn (Task $record): string =>
                        round($record->getTotalLoggedSeconds() / 60) . " min"
                    )
                    ->sortable(query: fn (Builder $query, string $direction) =>
                        $query->withCount([
                            'timeEntries as total_seconds' => fn ($q) =>
                                $q->selectRaw('COALESCE(SUM(duration_seconds), 0)')->whereNotNull('stopped_at')
                        ])->orderBy('total_seconds', $direction)
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(Task::getStatusOptions()),
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // logged_minutes supprimé de la table
                        $data['sort_order'] = 0;
                        return $data;
                    }),
            ])
            ->actions([
                Actions\EditAction::make(),

                Actions\Action::make('markAsDone')
                    ->label('Terminer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Task $record): bool => $record->status !== 'done')
                    ->action(function (Task $record) {
                        $record->update(['status' => 'done']);
                        Notification::make()
                            ->title('Tâche terminée')
                            ->success()
                            ->send();
                    }),

                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
    }
}
