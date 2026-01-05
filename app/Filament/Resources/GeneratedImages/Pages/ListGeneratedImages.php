<?php

namespace App\Filament\Resources\GeneratedImages\Pages;

use App\Filament\Resources\GeneratedImages\GeneratedImageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGeneratedImages extends ListRecords
{
    protected static string $resource = GeneratedImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
