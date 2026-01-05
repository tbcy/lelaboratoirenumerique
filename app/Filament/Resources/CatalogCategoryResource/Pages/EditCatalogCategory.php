<?php

namespace App\Filament\Resources\CatalogCategoryResource\Pages;

use App\Filament\Resources\CatalogCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCatalogCategory extends EditRecord
{
    protected static string $resource = CatalogCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
