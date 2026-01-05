<?php

namespace App\Filament\Resources\TaskResource\RelationManagers;

use App\Models\TimeEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TimeEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'timeEntries';

    protected static ?string $title = 'Sessions de temps';

    protected static ?string $modelLabel = 'session';

    protected static ?string $pluralModelLabel = 'sessions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('user_id')
                    ->label('Utilisateur')
                    ->relationship('user', 'name')
                    ->default(auth()->id())
                    ->required()
                    ->disabled(fn () => !auth()->user()->isAdmin()),

                Forms\Components\DateTimePicker::make('started_at')
                    ->label('Début')
                    ->required()
                    ->default(now())
                    ->seconds(false),

                Forms\Components\DateTimePicker::make('stopped_at')
                    ->label('Fin')
                    ->seconds(false)
                    ->after('started_at')
                    ->helperText('Laisser vide pour timer actif'),

                Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('started_at')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->sortable()
                    ->toggleable()
                    ->visible(fn () => auth()->user()->isAdmin()),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Début')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stopped_at')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('En cours')
                    ->color(fn ($state) => is_null($state) ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('formatted_duration')
                    ->label('Durée')
                    ->sortable(query: fn (Builder $query, string $direction) =>
                        $query->orderBy('duration_seconds', $direction)
                    ),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->getStateUsing(fn (TimeEntry $record) => $record->is_running ? 'En cours' : 'Terminé')
                    ->colors([
                        'success' => 'En cours',
                        'gray' => 'Terminé',
                    ]),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->toggleable()
                    ->placeholder('Aucune note'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Utilisateur')
                    ->relationship('user', 'name')
                    ->visible(fn () => auth()->user()->isAdmin()),

                Tables\Filters\Filter::make('status')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'running' => 'En cours',
                                'completed' => 'Terminé',
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
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['task_id'] = $this->ownerRecord->id;
                        return $data;
                    }),
            ])
            ->actions([
                Actions\ViewAction::make(),

                Actions\EditAction::make()
                    ->visible(fn (TimeEntry $record) =>
                        auth()->user()->isAdmin() ||
                        ($record->user_id === auth()->id() && !$record->stopped_at)
                    ),

                Actions\Action::make('stop')
                    ->label('Arrêter')
                    ->icon('heroicon-o-stop')
                    ->color('danger')
                    ->visible(fn (TimeEntry $record) => $record->is_running && $record->user_id === auth()->id())
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes'),
                    ])
                    ->action(function (TimeEntry $record, array $data) {
                        $record->stop($data['notes'] ?? null);
                    })
                    ->successNotificationTitle('Timer arrêté'),

                Actions\DeleteAction::make()
                    ->visible(fn (TimeEntry $record) =>
                        auth()->user()->isAdmin() || $record->user_id === auth()->id()
                    ),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('started_at', 'desc');
    }
}
