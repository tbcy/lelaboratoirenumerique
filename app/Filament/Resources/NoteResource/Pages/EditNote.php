<?php

namespace App\Filament\Resources\NoteResource\Pages;

use App\Filament\Resources\NoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNote extends EditRecord
{
    protected static string $resource = NoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_subpage')
                ->label(__('resources.note.actions.create_subpage'))
                ->icon('heroicon-o-document-plus')
                ->url(fn () => NoteResource::getUrl('create', ['parent_id' => $this->record->id])),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }
}
