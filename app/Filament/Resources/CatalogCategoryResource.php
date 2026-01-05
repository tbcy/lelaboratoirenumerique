<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CatalogCategoryResource\Pages;
use App\Filament\Resources\CatalogCategoryResource\RelationManagers;
use App\Models\CatalogCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CatalogCategoryResource extends Resource
{
    protected static ?string $model = CatalogCategory::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-folder';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('resources.catalog_category.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label(__('resources.catalog_category.name'))
                    ->required(),
                Forms\Components\ColorPicker::make('color')
                    ->label(__('resources.catalog_category.color')),
                Forms\Components\TextInput::make('sort_order')
                    ->label(__('resources.catalog_category.sort_order'))
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('resources.catalog_category.name'))
                    ->searchable(),
                Tables\Columns\ColorColumn::make('color')
                    ->label(__('resources.catalog_category.color')),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
                Actions\RestoreAction::make(),
                Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCatalogCategories::route('/'),
            'create' => Pages\CreateCatalogCategory::route('/create'),
            'edit' => Pages\EditCatalogCategory::route('/{record}/edit'),
        ];
    }
}
