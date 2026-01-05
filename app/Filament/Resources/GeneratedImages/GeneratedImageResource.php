<?php

namespace App\Filament\Resources\GeneratedImages;

use App\Filament\Resources\GeneratedImages\Pages\ListGeneratedImages;
use App\Filament\Resources\GeneratedImages\Tables\GeneratedImagesTable;
use App\Models\GeneratedImage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GeneratedImageResource extends Resource
{
    protected static ?string $model = GeneratedImage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.social_media');
    }

    public static function getModelLabel(): string
    {
        return __('resources.generated_image.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.generated_image.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return GeneratedImagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGeneratedImages::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
