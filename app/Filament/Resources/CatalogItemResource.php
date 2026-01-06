<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CatalogItemResource\Pages;
use App\Filament\Resources\CatalogItemResource\RelationManagers;
use App\Models\CatalogItem;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CatalogItemResource extends Resource
{
    protected static ?string $model = CatalogItem::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.billing');
    }

    public static function getModelLabel(): string
    {
        return __('resources.catalog_item.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.catalog_item.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.catalog_item.sections.information'))
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->label(__('resources.catalog_item.name'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('catalog_category_id')
                            ->label(__('resources.catalog_item.category'))
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('resources.catalog_item.category_name'))
                                    ->required(),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label(__('resources.catalog_item.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('resources.catalog_item.sections.pricing'))
                    ->components([
                        Forms\Components\TextInput::make('unit_price')
                            ->label(__('resources.catalog_item.unit_price'))
                            ->numeric()
                            ->prefix('EUR')
                            ->required()
                            ->minValue(0),

                        Forms\Components\Select::make('unit')
                            ->label(__('resources.catalog_item.unit'))
                            ->options(CatalogItem::getUnitOptions())
                            ->required()
                            ->default('unit')
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('vat_rate')
                            ->label(__('resources.catalog_item.vat_rate'))
                            ->numeric()
                            ->suffix('%')
                            ->required()
                            ->default(fn () => Company::first()?->default_vat_rate ?? 20)
                            ->minValue(0)
                            ->maxValue(100),

                        Forms\Components\TextInput::make('default_quantity')
                            ->label(__('resources.catalog_item.default_quantity'))
                            ->numeric()
                            ->default(1)
                            ->minValue(0.01)
                            ->step(0.01)
                            ->helperText(__('help.catalog_item.default_quantity')),

                        Forms\Components\Toggle::make('is_active')
                            ->label(__('resources.catalog_item.is_active'))
                            ->default(true)
                            ->helperText(__('help.catalog_item.inactive_note')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('resources.catalog_item.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('resources.catalog_item.category'))
                    ->sortable()
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label(__('resources.catalog_item.total_price'))
                    ->money('EUR')
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderByRaw('unit_price * default_quantity ' . $direction)),

                Tables\Columns\TextColumn::make('unit')
                    ->label(__('resources.catalog_item.unit'))
                    ->formatStateUsing(fn (string $state): string => CatalogItem::getUnitOptions()[$state] ?? $state)
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('default_quantity')
                    ->label(__('resources.catalog_item.default_quantity_short'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('vat_rate')
                    ->label(__('resources.catalog_item.vat_rate_short'))
                    ->formatStateUsing(fn ($state): string => "{$state}%")
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('resources.catalog_item.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('resources.catalog_item.created_at'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('catalog_category_id')
                    ->label(__('resources.catalog_item.category'))
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('resources.catalog_item.filters.status'))
                    ->placeholder(__('resources.catalog_item.filters.all'))
                    ->trueLabel(__('resources.catalog_item.filters.active'))
                    ->falseLabel(__('resources.catalog_item.filters.inactive')),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\Action::make('duplicate')
                    ->label(__('resources.catalog_item.actions.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (CatalogItem $record) {
                        $newItem = $record->replicate();
                        $newItem->name = $record->name . __('resources.catalog_item.actions.copy_suffix');
                        $newItem->save();
                    }),
                Actions\DeleteAction::make(),
                Actions\RestoreAction::make(),
                Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),

                    Actions\BulkAction::make('activate')
                        ->label(__('resources.catalog_item.actions.activate'))
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['is_active' => true])))
                        ->deselectRecordsAfterCompletion(),

                    Actions\BulkAction::make('deactivate')
                        ->label(__('resources.catalog_item.actions.deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['is_active' => false])))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListCatalogItems::route('/'),
            'create' => Pages\CreateCatalogItem::route('/create'),
            'edit' => Pages\EditCatalogItem::route('/{record}/edit'),
        ];
    }
}
