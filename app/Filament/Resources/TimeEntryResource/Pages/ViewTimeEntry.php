<?php

namespace App\Filament\Resources\TimeEntryResource\Pages;

use App\Filament\Resources\TimeEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Schemas\Schema;

class ViewTimeEntry extends ViewRecord
{
    protected static string $resource = TimeEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Infolists\Components\Section::make('Informations')
                    ->schema([
                        Infolists\Components\TextEntry::make('task.title')
                            ->label('Tâche')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('task.project.name')
                            ->label('Projet'),

                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Utilisateur')
                            ->visible(fn () => auth()->user()->isAdmin()),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Temps')
                    ->schema([
                        Infolists\Components\TextEntry::make('started_at')
                            ->label('Début')
                            ->dateTime('d/m/Y H:i'),

                        Infolists\Components\TextEntry::make('stopped_at')
                            ->label('Fin')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('En cours')
                            ->badge()
                            ->color(fn ($state) => is_null($state) ? 'success' : 'gray'),

                        Infolists\Components\TextEntry::make('formatted_duration')
                            ->label('Durée')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('notes')
                            ->label('Notes')
                            ->columnSpanFull()
                            ->placeholder('Aucune note'),
                    ])
                    ->columns(3),
            ]);
    }
}
