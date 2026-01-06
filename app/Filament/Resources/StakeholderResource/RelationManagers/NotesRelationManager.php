<?php

namespace App\Filament\Resources\StakeholderResource\RelationManagers;

use App\Filament\Resources\NoteResource;
use App\Filament\Traits\StandardTableConfig;
use App\Models\Note;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;

class NotesRelationManager extends RelationManager
{
    use StandardTableConfig;

    protected static string $relationship = 'notes';

    protected static ?string $title = 'Notes';

    protected static ?string $modelLabel = 'Note';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('resources.note.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->description(fn (Note $record): ?string =>
                        $record->parent ? "↳ {$record->parent->name}" : null
                    ),

                Tables\Columns\TextColumn::make('datetime')
                    ->label(__('resources.note.datetime'))
                    ->dateTime(self::DATETIME_FORMAT)
                    ->sortable(),

                Tables\Columns\TextColumn::make('scopes.name')
                    ->label(__('resources.note.scopes'))
                    ->badge()
                    ->color('primary')
                    ->wrap(),

                Tables\Columns\TextColumn::make('short_summary')
                    ->label(__('resources.note.short_summary'))
                    ->html()
                    ->wrap()
                    ->words(20)
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('scopes')
                    ->label(__('resources.note.filters.scope'))
                    ->relationship('scopes', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->headerActions([
                Actions\AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Actions\Action::make('view')
                    ->label(__('resources.common.view'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (Note $record): string => NoteResource::getUrl('edit', ['record' => $record])),

                Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DetachBulkAction::make(),
                ]),
            ])
            ->defaultSort('datetime', 'desc');
    }
}
