<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.clients');
    }

    public static function getModelLabel(): string
    {
        return __('resources.client.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.client.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.client.sections.main_information'))
                    ->components([
                        Forms\Components\Select::make('type')
                            ->label(__('resources.client.type'))
                            ->options([
                                'company' => __('enums.client_type.company'),
                                'individual' => __('enums.client_type.individual'),
                            ])
                            ->required()
                            ->live()
                            ->default('company')
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('company_name')
                            ->label(__('resources.client.company_name'))
                            ->visible(fn (Get $get) => $get('type') === 'company')
                            ->required(fn (Get $get) => $get('type') === 'company')
                            ->maxLength(255),

                        Grid::make(2)
                            ->components([
                                Forms\Components\TextInput::make('first_name')
                                    ->label(__('resources.client.first_name'))
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('last_name')
                                    ->label(__('resources.client.last_name'))
                                    ->maxLength(255),
                            ]),

                        Grid::make(2)
                            ->components([
                                Forms\Components\TextInput::make('email')
                                    ->label(__('resources.client.email'))
                                    ->email()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->label(__('resources.client.phone'))
                                    ->tel()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\TextInput::make('mobile')
                            ->label(__('resources.client.mobile'))
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\Select::make('status')
                            ->label(__('resources.client.status'))
                            ->options([
                                'prospect' => __('enums.client_status.prospect'),
                                'active' => __('enums.client_status.active'),
                                'inactive' => __('enums.client_status.inactive'),
                            ])
                            ->default('prospect')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TagsInput::make('tags')
                            ->label(__('resources.client.tags'))
                            ->separator(','),
                    ])
                    ->columns(2),

                Section::make(__('resources.client.sections.address'))
                    ->components([
                        Forms\Components\TextInput::make('address')
                            ->label(__('resources.client.address'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('address_2')
                            ->label(__('resources.client.address_2'))
                            ->maxLength(255),
                        Grid::make(3)
                            ->components([
                                Forms\Components\TextInput::make('postal_code')
                                    ->label(__('resources.client.postal_code'))
                                    ->maxLength(10),
                                Forms\Components\TextInput::make('city')
                                    ->label(__('resources.client.city'))
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('country')
                                    ->label(__('resources.client.country'))
                                    ->default('France')
                                    ->maxLength(255),
                            ]),
                    ])
                    ->collapsible(),

                Section::make(__('resources.client.sections.tax_information'))
                    ->components([
                        Forms\Components\TextInput::make('siret')
                            ->label(__('resources.client.siret'))
                            ->maxLength(14),
                        Forms\Components\TextInput::make('vat_number')
                            ->label(__('resources.client.vat_number'))
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->visible(fn (Get $get) => $get('type') === 'company'),

                Section::make(__('resources.client.sections.notes'))
                    ->components([
                        Forms\Components\Textarea::make('notes')
                            ->label(__('resources.client.notes'))
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
                Tables\Columns\TextColumn::make('display_name')
                    ->label(__('resources.client.display_name'))
                    ->searchable(['company_name', 'first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('resources.client.email'))
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('resources.client.phone'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label(__('resources.client.city'))
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('resources.client.status'))
                    ->colors([
                        'warning' => 'prospect',
                        'success' => 'active',
                        'danger' => 'inactive',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'prospect' => __('enums.client_status.prospect'),
                        'active' => __('enums.client_status.active'),
                        'inactive' => __('enums.client_status.inactive'),
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('quotes_count')
                    ->label(__('resources.client.quotes_count'))
                    ->counts('quotes')
                    ->badge(),
                Tables\Columns\TextColumn::make('invoices_count')
                    ->label(__('resources.client.invoices_count'))
                    ->counts('invoices')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('resources.client.created_at'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('resources.client.status'))
                    ->options([
                        'prospect' => __('enums.client_status.prospect'),
                        'active' => __('enums.client_status.active'),
                        'inactive' => __('enums.client_status.inactive'),
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('resources.client.type'))
                    ->options([
                        'company' => __('enums.client_type.company'),
                        'individual' => __('enums.client_type.individual'),
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
