<?php

namespace App\Filament\Resources\CatalogCategoryResource\Pages;

use App\Filament\Resources\CatalogCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCatalogCategories extends ListRecords
{
    protected static string $resource = CatalogCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
