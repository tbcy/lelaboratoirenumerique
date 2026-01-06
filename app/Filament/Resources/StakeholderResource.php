<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StakeholderResource\Pages;
use App\Models\Stakeholder;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StakeholderResource extends Resource
{
    protected static ?string $model = Stakeholder::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.notes');
    }

    public static function getModelLabel(): string
    {
        return __('resources.stakeholder.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.stakeholder.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.stakeholder.sections.main_information'))
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->label(__('resources.stakeholder.name'))
                            ->required()
                            ->maxLength(255),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                    ->label(__('resources.stakeholder.email'))
                                    ->email()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('phone')
                                    ->label(__('resources.stakeholder.phone'))
                                    ->tel()
                                    ->maxLength(255),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('company')
                                    ->label(__('resources.stakeholder.company'))
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('role')
                                    ->label(__('resources.stakeholder.role'))
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Toggle::make('is_active')
                            ->label(__('resources.stakeholder.is_active'))
                            ->default(true),
                    ]),

                Section::make(__('resources.stakeholder.sections.internal_notes'))
                    ->components([
                        Forms\Components\Textarea::make('notes')
                            ->label(__('resources.stakeholder.notes'))
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('resources.stakeholder.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label(__('resources.stakeholder.email'))
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('company')
                    ->label(__('resources.stakeholder.company'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('role')
                    ->label(__('resources.stakeholder.role'))
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('resources.stakeholder.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('notes_count')
                    ->label(__('resources.stakeholder.notes_count'))
                    ->counts('notes')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tasks_count')
                    ->label(__('resources.stakeholder.tasks_count'))
                    ->counts('tasks')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('resources.stakeholder.created_at'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('resources.stakeholder.filters.active')),

                Tables\Filters\SelectFilter::make('company')
                    ->label(__('resources.stakeholder.filters.company'))
                    ->options(fn () => Stakeholder::whereNotNull('company')
                        ->distinct()
                        ->pluck('company', 'company')
                        ->toArray()
                    ),

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
            ])
            ->defaultSort('name');
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStakeholders::route('/'),
            'create' => Pages\CreateStakeholder::route('/create'),
            'edit' => Pages\EditStakeholder::route('/{record}/edit'),
        ];
    }
}
