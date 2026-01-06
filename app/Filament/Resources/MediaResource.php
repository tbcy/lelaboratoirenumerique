<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 99;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.media');
    }

    public static function getModelLabel(): string
    {
        return __('resources.media.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.media.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->label(__('resources.media.name'))
                            ->required(),

                        Forms\Components\TextInput::make('file_name')
                            ->label(__('resources.media.file_name'))
                            ->disabled(),

                        Forms\Components\TextInput::make('mime_type')
                            ->label(__('resources.media.mime_type'))
                            ->disabled(),

                        Forms\Components\TextInput::make('size')
                            ->label(__('resources.media.size'))
                            ->formatStateUsing(fn ($state) => number_format($state / 1024, 2) . ' KB')
                            ->disabled(),

                        Forms\Components\TextInput::make('collection_name')
                            ->label(__('resources.media.collection'))
                            ->disabled(),

                        Forms\Components\TextInput::make('model_type')
                            ->label(__('resources.media.model_type'))
                            ->formatStateUsing(fn ($state) => class_basename($state))
                            ->disabled(),

                        Forms\Components\TextInput::make('model_id')
                            ->label(__('resources.media.model_id'))
                            ->disabled(),

                        Forms\Components\Textarea::make('custom_properties')
                            ->label(__('resources.media.custom_properties'))
                            ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT))
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('preview')
                    ->label('')
                    ->getStateUsing(fn (Media $record): ?string =>
                        str_starts_with($record->mime_type, 'image/') ? $record->getUrl() : null
                    )
                    ->size(50)
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('resources.media.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('file_name')
                    ->label(__('resources.media.file_name'))
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('collection_name')
                    ->label(__('resources.media.collection'))
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('model_type')
                    ->label(__('resources.media.model'))
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('size')
                    ->label(__('resources.media.size'))
                    ->formatStateUsing(fn ($state) => number_format($state / 1024, 2) . ' KB')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('resources.media.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('collection_name')
                    ->label(__('resources.media.collection'))
                    ->options(fn () => Media::distinct()->pluck('collection_name', 'collection_name')->toArray()),

                Tables\Filters\SelectFilter::make('model_type')
                    ->label(__('resources.media.model'))
                    ->options(fn () => Media::distinct()
                        ->pluck('model_type')
                        ->mapWithKeys(fn ($type) => [$type => class_basename($type)])
                        ->toArray()
                    ),

                Tables\Filters\Filter::make('images_only')
                    ->label(__('resources.media.images_only'))
                    ->query(fn ($query) => $query->where('mime_type', 'like', 'image/%')),
            ])
            ->actions([
                Actions\Action::make('download')
                    ->label(__('resources.media.actions.download'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (Media $record) => response()->download($record->getPath(), $record->file_name)),

                Actions\Action::make('view')
                    ->label(__('resources.media.actions.view'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (Media $record) => $record->getUrl())
                    ->openUrlInNewTab()
                    ->visible(fn (Media $record) => str_starts_with($record->mime_type, 'image/')),

                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
