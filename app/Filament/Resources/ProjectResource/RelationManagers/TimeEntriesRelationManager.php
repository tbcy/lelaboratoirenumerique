<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\TaskResource;
use App\Models\TimeEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TimeEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'timeEntries';

    protected static ?string $title = 'Temps passé par tâche';

    protected static ?string $modelLabel = 'temps';

    protected static ?string $pluralModelLabel = 'temps';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TimeEntry::query()
                    ->select([
                        'task_id',
                        DB::raw('COUNT(*) as entries_count'),
                        DB::raw('SUM(duration_seconds) as total_seconds'),
                        DB::raw('MAX(stopped_at) as last_activity'),
                    ])
                    ->whereHas('task', fn($q) => $q->where('project_id', $this->ownerRecord->id))
                    ->whereNotNull('stopped_at')
                    ->groupBy('task_id')
                    ->with('task')
            )
            ->columns([
                Tables\Columns\TextColumn::make('task.title')
                    ->label('Tâche')
                    ->weight('bold')
                    ->searchable()
                    ->url(fn ($record) => $record->task ? TaskResource::getUrl('edit', ['record' => $record->task_id]) : null),

                Tables\Columns\TextColumn::make('total_duration')
                    ->label('Temps total')
                    ->getStateUsing(function ($record) {
                        $hours = floor($record->total_seconds / 3600);
                        $minutes = floor(($record->total_seconds % 3600) / 60);
                        return sprintf('%02d:%02d (%0.2fh)', $hours, $minutes, $record->total_seconds / 3600);
                    })
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('entries_count')
                    ->label('Sessions')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('last_activity')
                    ->label('Dernière activité')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Progression')
                    ->getStateUsing(function ($record) {
                        $task = $record->task;
                        if (!$task || !$task->estimated_minutes) {
                            return 'N/A';
                        }

                        $logged = $record->total_seconds / 60;
                        $percent = min(100, round(($logged / $task->estimated_minutes) * 100));
                        return "{$percent}%";
                    })
                    ->badge()
                    ->color(function ($record) {
                        $task = $record->task;
                        if (!$task || !$task->estimated_minutes) {
                            return 'gray';
                        }
                        $logged = $record->total_seconds / 60;
                        $percent = min(100, round(($logged / $task->estimated_minutes) * 100));
                        return $percent >= 100 ? 'success' : 'primary';
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Actions\Action::make('view_details')
                    ->label('Voir détails')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn ($record) => 'Détails - ' . $record->task->title)
                    ->modalContent(function ($record) {
                        $entries = TimeEntry::where('task_id', $record->task_id)
                            ->whereNotNull('stopped_at')
                            ->with('user')
                            ->orderBy('started_at', 'desc')
                            ->get();

                        return view('filament.tables.modals.time-entries-details', [
                            'entries' => $entries,
                            'task' => $record->task,
                        ]);
                    })
                    ->modalWidth('2xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer'),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('last_activity', 'desc');
    }
}
