<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialConnectionResource\Pages;
use App\Filament\Resources\SocialConnectionResource\RelationManagers;
use App\Models\SocialConnection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SocialConnectionResource extends Resource
{
    protected static ?string $model = SocialConnection::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-link';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.social_media');
    }

    public static function getModelLabel(): string
    {
        return __('resources.social_connection.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.social_connection.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.social_connection.sections.general_information'))
                    ->components([
                        Forms\Components\TextInput::make('name')
                            ->label(__('resources.social_connection.name'))
                            ->required()
                            ->maxLength(255)
                            ->helperText(__('help.social_connection.name')),

                        Forms\Components\Select::make('platform')
                            ->label(__('resources.social_connection.platform'))
                            ->options(SocialConnection::getPlatformOptions())
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive(),

                        Forms\Components\Toggle::make('is_active')
                            ->label(__('resources.social_connection.is_active'))
                            ->default(true),
                    ]),

                Section::make(__('resources.social_connection.sections.credentials'))
                    ->description(fn (Get $get): ?string => match ($get('platform')) {
                        'twitter' => __('resources.social_connection.platform_descriptions.twitter'),
                        'linkedin' => __('resources.social_connection.platform_descriptions.linkedin'),
                        'instagram' => __('resources.social_connection.platform_descriptions.instagram'),
                        'facebook' => __('resources.social_connection.platform_descriptions.facebook'),
                        default => null,
                    })
                    ->components([
                        // X/Twitter credentials (OAuth 1.0a)
                        Group::make()
                            ->components([
                                Forms\Components\TextInput::make('credentials.api_key')
                                    ->label('API Key (Consumer Key)')
                                    ->required()
                                    ->password()
                                    ->revealable()
                                    ->helperText(__('help.social_connection.x_api_key')),

                                Forms\Components\TextInput::make('credentials.api_secret')
                                    ->label('API Secret (Consumer Secret)')
                                    ->required()
                                    ->password()
                                    ->revealable()
                                    ->helperText(__('help.social_connection.x_api_secret')),

                                Forms\Components\TextInput::make('credentials.access_token')
                                    ->label('Access Token')
                                    ->required()
                                    ->password()
                                    ->revealable()
                                    ->helperText(__('help.social_connection.x_access_token')),

                                Forms\Components\TextInput::make('credentials.access_token_secret')
                                    ->label('Access Token Secret')
                                    ->required()
                                    ->password()
                                    ->revealable()
                                    ->helperText(__('help.social_connection.x_access_token_secret')),
                            ])
                            ->visible(fn (Get $get): bool => $get('platform') === 'twitter')
                            ->columns(2),

                        // LinkedIn credentials (OAuth 2.0)
                        Group::make()
                            ->components([
                                Forms\Components\TextInput::make('credentials.client_id')
                                    ->label('Client ID')
                                    ->required()
                                    ->password()
                                    ->revealable()
                                    ->helperText(__('help.social_connection.linkedin_client_id')),

                                Forms\Components\TextInput::make('credentials.client_secret')
                                    ->label('Client Secret')
                                    ->required()
                                    ->password()
                                    ->revealable()
                                    ->helperText(__('help.social_connection.linkedin_client_secret')),

                                Forms\Components\TextInput::make('credentials.access_token')
                                    ->label('Access Token')
                                    ->password()
                                    ->revealable()
                                    ->helperText(__('help.social_connection.linkedin_access_token')),

                                Forms\Components\TextInput::make('credentials.refresh_token')
                                    ->label('Refresh Token')
                                    ->password()
                                    ->revealable()
                                    ->helperText('Token de renouvellement (pour régénération automatique)'),

                                Forms\Components\DateTimePicker::make('credentials.expires_at')
                                    ->label('Expiration du token')
                                    ->helperText('Date d\'expiration de l\'access token')
                                    ->displayFormat('d/m/Y H:i')
                                    ->native(false),

                                Forms\Components\TextInput::make('credentials.redirect_uri')
                                    ->label('Redirect URI')
                                    ->url()
                                    ->helperText(__('help.social_connection.linkedin_redirect_uri'))
                                    ->columnSpanFull(),
                            ])
                            ->visible(fn (Get $get): bool => $get('platform') === 'linkedin')
                            ->columns(2),

                        // Instagram credentials (Facebook Graph API)
                        Group::make()
                            ->components([
                                Forms\Components\TextInput::make('credentials.access_token')
                                    ->label('Access Token (Long-lived)')
                                    ->required()
                                    ->password()
                                    ->revealable()
                                    ->helperText(__('help.social_connection.instagram_access_token'))
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('credentials.business_account_id')
                                    ->label('Instagram Business Account ID')
                                    ->required()
                                    ->helperText(__('help.social_connection.instagram_business_account_id')),
                            ])
                            ->visible(fn (Get $get): bool => $get('platform') === 'instagram')
                            ->columns(2),

                        // Facebook credentials
                        Group::make()
                            ->components([
                                Forms\Components\TextInput::make('credentials.page_access_token')
                                    ->label('Page Access Token')
                                    ->required()
                                    ->password()
                                    ->revealable()
                                    ->helperText(__('help.social_connection.facebook_access_token'))
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('credentials.page_id')
                                    ->label('Page ID')
                                    ->required()
                                    ->helperText(__('help.social_connection.facebook_page_id')),
                            ])
                            ->visible(fn (Get $get): bool => $get('platform') === 'facebook')
                            ->columns(2),

                        // Placeholder if no platform selected
                        Forms\Components\Placeholder::make('select_platform')
                            ->label(__('resources.social_connection.select_platform'))
                            ->content(__('resources.social_connection.credentials_placeholder'))
                            ->visible(fn (Get $get): bool => $get('platform') === null),
                    ]),

                Section::make(__('resources.social_connection.sections.information'))
                    ->components([
                        Forms\Components\Placeholder::make('last_used_at')
                            ->label(__('resources.social_connection.last_used_at'))
                            ->content(fn (?SocialConnection $record): string =>
                                $record?->last_used_at?->diffForHumans() ?? __('resources.social_connection.never_used')
                            ),
                    ])
                    ->visible(fn (?SocialConnection $record): bool => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('resources.social_connection.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('platform')
                    ->label(__('resources.social_connection.platform'))
                    ->formatStateUsing(fn (string $state): string =>
                        SocialConnection::getPlatformOptions()[$state] ?? ucfirst($state)
                    )
                    ->colors([
                        'primary' => 'twitter',
                        'info' => 'linkedin',
                        'danger' => 'instagram',
                        'success' => 'facebook',
                    ]),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('resources.social_connection.active'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('last_used_at')
                    ->label(__('resources.social_connection.last_used_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder(__('resources.social_connection.never')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('resources.social_connection.created_at'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform')
                    ->label(__('resources.social_connection.platform'))
                    ->options(SocialConnection::getPlatformOptions()),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('resources.social_connection.active')),

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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListSocialConnections::route('/'),
            'create' => Pages\CreateSocialConnection::route('/create'),
            'edit' => Pages\EditSocialConnection::route('/{record}/edit'),
        ];
    }
}
