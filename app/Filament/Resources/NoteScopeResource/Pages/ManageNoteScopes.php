<?php

namespace App\Filament\Resources\NoteScopeResource\Pages;

use App\Filament\Resources\NoteScopeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageNoteScopes extends ManageRecords
{
    protected static string $resource = NoteScopeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
