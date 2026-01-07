<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialPostResource\Pages;
use App\Filament\Resources\SocialPostResource\RelationManagers;
use App\Models\SocialPost;
use App\Models\SocialConnection;
use App\Services\DalleImageService;
use App\Services\Social\TwitterConnector;
use App\Services\Social\LinkedInConnector;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use App\Filament\Traits\StandardTableConfig;

class SocialPostResource extends Resource
{
    use StandardTableConfig;

    protected static ?string $model = SocialPost::class;

    protected static ?string $recordTitleAttribute = 'content';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.communication');
    }

    public static function getModelLabel(): string
    {
        return __('resources.social_post.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.social_post.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->components([
                        Section::make(__('resources.social_post.sections.content'))
                            ->components([
                                Forms\Components\Textarea::make('content')
                                    ->label(__('resources.social_post.content'))
                                    ->required()
                                    ->rows(6)
                                    ->maxLength(2200)
                                    ->helperText(fn ($state) => __('resources.social_post.character_count', ['count' => strlen($state ?? '')]))
                                    ->columnSpanFull(),

                                Forms\Components\FileUpload::make('images')
                                    ->label(__('resources.social_post.images'))
                                    ->multiple()
                                    ->image()
                                    ->disk('local')
                                    ->directory('social-posts')
                                    ->visibility('private')
                                    ->maxFiles(4)
                                    ->reorderable()
                                    ->columnSpanFull()
                                    ->hintAction(
                                        Actions\Action::make('generateImage')
                                            ->label(__('resources.social_post.actions.generate_image'))
                                            ->icon('heroicon-o-sparkles')
                                            ->color('info')
                                            ->requiresConfirmation()
                                            ->modalHeading(__('resources.social_post.modals.generate_heading'))
                                            ->modalDescription(__('resources.social_post.modals.generate_description'))
                                            ->modalSubmitActionLabel(__('resources.social_post.actions.generate'))
                                            ->disabled(fn (Get $get): bool => empty(trim($get('content') ?? '')))
                                            ->action(function (Get $get, Set $set) {
                                                $content = $get('content');

                                                if (empty(trim($content))) {
                                                    Notification::make()
                                                        ->title(__('resources.social_post.notifications.content_required'))
                                                        ->danger()
                                                        ->send();
                                                    return;
                                                }

                                                $service = new DalleImageService();

                                                if (! $service->isConfigured()) {
                                                    Notification::make()
                                                        ->title(__('resources.social_post.notifications.openai_not_configured'))
                                                        ->body(__('resources.social_post.notifications.openai_not_configured_body'))
                                                        ->danger()
                                                        ->send();
                                                    return;
                                                }

                                                Notification::make()
                                                    ->title(__('resources.social_post.notifications.generating_image'))
                                                    ->info()
                                                    ->send();

                                                $result = $service->generate($content);

                                                if ($result['success']) {
                                                    // Copy image to social-posts directory
                                                    $image = $result['image'];
                                                    $socialPostPath = 'social-posts/' . $image->file_name;
                                                    \Illuminate\Support\Facades\Storage::disk('local')->copy(
                                                        $image->file_path,
                                                        $socialPostPath
                                                    );

                                                    // Add to images array
                                                    $currentImages = $get('images') ?? [];
                                                    $currentImages[] = $socialPostPath;
                                                    $set('images', $currentImages);

                                                    Notification::make()
                                                        ->title(__('resources.social_post.notifications.image_generated'))
                                                        ->success()
                                                        ->send();
                                                } else {
                                                    Notification::make()
                                                        ->title(__('resources.social_post.notifications.image_generation_failed'))
                                                        ->body($result['error'])
                                                        ->danger()
                                                        ->send();
                                                }
                                            })
                                    ),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->components([
                        Section::make(__('resources.social_post.sections.publication'))
                            ->components([
                                Forms\Components\CheckboxList::make('connection_ids')
                                    ->label(__('resources.social_post.connections'))
                                    ->options(SocialConnection::where('is_active', true)->get()->pluck('display_name', 'id'))
                                    ->required()
                                    ->columns(1),

                                Forms\Components\Select::make('status')
                                    ->label(__('resources.social_post.status'))
                                    ->options(SocialPost::getStatusOptions())
                                    ->default('draft')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn (?SocialPost $record): bool => $record !== null && in_array($record->status, ['scheduled', 'published'])),

                                Forms\Components\DateTimePicker::make('scheduled_at')
                                    ->label(__('resources.social_post.scheduled_for'))
                                    ->visible(fn (Get $get): bool => in_array($get('status'), ['approved', 'scheduled'])),
                            ]),

                        Section::make(__('resources.social_post.sections.information'))
                            ->components([
                                Forms\Components\Placeholder::make('published_info')
                                    ->label(__('resources.social_post.published_at'))
                                    ->content(fn (?SocialPost $record): string => $record?->published_at?->format(self::DATETIME_FORMAT) ?? '-'),

                                Forms\Components\Placeholder::make('error')
                                    ->label(__('resources.social_post.error'))
                                    ->content(fn (?SocialPost $record): string => $record?->error_message ?? '-')
                                    ->visible(fn (?SocialPost $record): bool => $record?->status === 'failed'),
                            ])
                            ->visible(fn (?SocialPost $record): bool => $record !== null && $record->status !== 'draft'),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('content')
                    ->label(__('resources.social_post.content_table'))
                    ->limit(50)
                    ->searchable()
                    ->wrap(),

                Tables\Columns\ImageColumn::make('images')
                    ->label(__('resources.social_post.images'))
                    ->disk('local')
                    ->visibility('private')
                    ->circular()
                    ->stacked()
                    ->limit(3),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('resources.social_post.status'))
                    ->badge()
                    ->color(fn (string $state): string => self::getStatusColor($state))
                    ->formatStateUsing(fn (string $state): string => SocialPost::getStatusOptions()[$state] ?? $state),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label(__('resources.social_post.scheduled'))
                    ->dateTime(self::DATETIME_FORMAT)
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('resources.social_post.published'))
                    ->dateTime(self::DATETIME_FORMAT)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('resources.social_post.created_at'))
                    ->date(self::DATE_FORMAT)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('resources.social_post.status'))
                    ->options(SocialPost::getStatusOptions()),

                Tables\Filters\Filter::make('pending_approval')
                    ->label(__('resources.social_post.filters.pending_approval'))
                    ->query(fn (Builder $query): Builder => $query->where('status', 'draft')),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),

                    Actions\Action::make('approve')
                        ->label(__('resources.social_post.actions.approve'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (SocialPost $record): bool => $record->status === 'draft')
                        ->action(function (SocialPost $record) {
                            $record->approve();
                            Notification::make()
                                ->title(__('resources.social_post.notifications.approved'))
                                ->success()
                                ->send();
                        }),

                    Actions\Action::make('reject')
                        ->label(__('resources.social_post.actions.reject'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (SocialPost $record): bool => in_array($record->status, ['draft', 'approved']))
                        ->action(function (SocialPost $record) {
                            $record->reject();
                            Notification::make()
                                ->title(__('resources.social_post.notifications.rejected'))
                                ->warning()
                                ->send();
                        }),

                    Actions\Action::make('schedule')
                        ->label(__('resources.social_post.actions.schedule'))
                        ->icon('heroicon-o-clock')
                        ->color('info')
                        ->visible(fn (SocialPost $record): bool => $record->status === 'approved')
                        ->form([
                            Forms\Components\DateTimePicker::make('scheduled_at')
                                ->label(__('resources.social_post.datetime'))
                                ->required()
                                ->minDate(now()),
                        ])
                        ->action(function (SocialPost $record, array $data) {
                            $record->update([
                                'scheduled_at' => $data['scheduled_at'],
                                'status' => 'scheduled',
                            ]);
                            Notification::make()
                                ->title(__('resources.social_post.notifications.scheduled'))
                                ->success()
                                ->send();
                        }),

                    Actions\Action::make('publish')
                        ->label(__('resources.social_post.actions.publish_now'))
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading(__('resources.social_post.modals.publish_heading'))
                        ->modalDescription(__('resources.social_post.modals.publish_description'))
                        ->visible(fn (SocialPost $record): bool => in_array($record->status, ['approved', 'draft']))
                        ->action(function (SocialPost $record) {
                            // Récupérer toutes les connexions actives sélectionnées
                            $connections = SocialConnection::whereIn('id', $record->connection_ids ?? [])
                                ->where('is_active', true)
                                ->get();

                            if ($connections->isEmpty()) {
                                Notification::make()
                                    ->title(__('resources.social_post.notifications.no_active_connection'))
                                    ->body(__('resources.social_post.notifications.no_active_connection_body'))
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $published = 0;
                            $errors = [];
                            $publishedPlatforms = [];

                            foreach ($connections as $connection) {
                                try {
                                    $connector = null;
                                    $result = null;

                                    // Utiliser le bon connecteur selon la plateforme
                                    switch ($connection->platform) {
                                        case 'twitter':
                                            $connector = new TwitterConnector($connection);
                                            $result = $connector->publishTweet($record);
                                            break;

                                        case 'linkedin':
                                            $connector = new LinkedInConnector($connection);
                                            $result = $connector->publishPost($record);
                                            break;

                                        default:
                                            $errors[] = __('resources.social_post.notifications.unsupported_platform', ['platform' => $connection->platform]);
                                            continue 2;
                                    }

                                    if ($result['success']) {
                                        $published++;
                                        $publishedPlatforms[] = $connection->platform;
                                    } else {
                                        $errors[] = "{$connection->name}: {$result['error']}";
                                    }
                                } catch (\Exception $e) {
                                    $errors[] = "{$connection->name}: {$e->getMessage()}";
                                }
                            }

                            if ($published > 0) {
                                $record->markAsPublished();
                                $platforms = implode(', ', array_unique($publishedPlatforms));
                                Notification::make()
                                    ->title(__('resources.social_post.notifications.published_success'))
                                    ->body(__('resources.social_post.notifications.published_body', ['count' => $published, 'platforms' => $platforms]))
                                    ->success()
                                    ->send();
                            } else {
                                $record->markAsFailed(implode(', ', $errors));
                                Notification::make()
                                    ->title(__('resources.social_post.notifications.published_failed'))
                                    ->body(__('resources.social_post.notifications.published_failed_body', ['errors' => implode('; ', $errors)]))
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Actions\Action::make('duplicate')
                        ->label(__('resources.social_post.actions.duplicate'))
                        ->icon('heroicon-o-document-duplicate')
                        ->action(function (SocialPost $record) {
                            $newPost = $record->replicate();
                            $newPost->status = 'draft';
                            $newPost->scheduled_at = null;
                            $newPost->published_at = null;
                            $newPost->error_message = null;
                            $newPost->save();

                            Notification::make()
                                ->title(__('resources.social_post.notifications.duplicated'))
                                ->success()
                                ->send();
                        }),

                    Actions\DeleteAction::make(),
                    Actions\RestoreAction::make(),
                    Actions\ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),

                    Actions\BulkAction::make('approve')
                        ->label(__('resources.social_post.actions.approve'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(fn ($record) => $record->status === 'draft' && $record->approve());
                            Notification::make()
                                ->title(__('resources.social_post.notifications.bulk_approved'))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListSocialPosts::route('/'),
            'create' => Pages\CreateSocialPost::route('/create'),
            'edit' => Pages\EditSocialPost::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'draft')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['content'];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return \Illuminate\Support\Str::limit($record->content, 50);
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            __('resources.social_post.status') => SocialPost::getStatusOptions()[$record->status] ?? $record->status,
            __('resources.social_post.scheduled') => $record->scheduled_at?->format('d/m/Y H:i') ?? '-',
        ];
    }
}
