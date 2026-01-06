<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.communication');
    }

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return __('resources.post.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.post.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                // Colonne principale (2/3)
                Section::make(__('resources.post.sections.content'))
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        TextInput::make('title')
                            ->label(__('resources.post.title'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state, ?string $old) {
                                if (($old ?? '') !== $state) {
                                    $set('slug', Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label(__('resources.post.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Textarea::make('excerpt')
                            ->label(__('resources.post.excerpt'))
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText(__('resources.post.helpers.excerpt')),

                        Toggle::make('html_mode')
                            ->label(__('resources.post.html_mode'))
                            ->helperText(__('resources.post.helpers.html_mode'))
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function (Set $set, $get, $state) {
                                // Force refresh du contenu lors du switch
                                $content = $get('content');
                                $set('content', $content);
                            })
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->label(__('resources.post.content'))
                            ->required()
                            ->columnSpanFull()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('blog-attachments')
                            ->fileAttachmentsVisibility('public')
                            ->toolbarButtons([
                                'attachFiles',
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'h2',
                                'h3',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'underline',
                                'undo',
                            ])
                            ->hidden(fn ($get) => $get('html_mode')),

                        Textarea::make('content_html')
                            ->label(__('resources.post.content_html'))
                            ->required()
                            ->rows(20)
                            ->columnSpanFull()
                            ->helperText(__('resources.post.helpers.content_html'))
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($get) => $get('content'))
                            ->afterStateUpdated(fn (Set $set, $state) => $set('content', $state))
                            ->live(onBlur: true)
                            ->hidden(fn ($get) => ! $get('html_mode')),
                    ]),

                // Colonne latérale (1/3)
                Grid::make(1)
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Section::make(__('resources.post.sections.status'))
                            ->schema([
                                Select::make('status')
                                    ->label(__('resources.post.status'))
                                    ->options([
                                        'draft' => __('resources.post.statuses.draft'),
                                        'published' => __('resources.post.statuses.published'),
                                    ])
                                    ->default('draft')
                                    ->required(),

                                DateTimePicker::make('published_at')
                                    ->label(__('resources.post.published_at'))
                                    ->default(now()),

                                Toggle::make('is_featured')
                                    ->label(__('resources.post.is_featured'))
                                    ->helperText(__('resources.post.is_featured_helper')),
                            ]),

                        Section::make(__('resources.post.sections.organization'))
                            ->schema([
                                Select::make('category_id')
                                    ->label(__('resources.post.category'))
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label(__('resources.post.name'))
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                        TextInput::make('slug')
                                            ->label(__('resources.post.slug'))
                                            ->required()
                                            ->maxLength(255),
                                        ColorPicker::make('color')
                                            ->label(__('resources.post.color')),
                                    ]),

                                Select::make('tags')
                                    ->label(__('resources.post.tags'))
                                    ->relationship('tags', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label(__('resources.post.name'))
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                        TextInput::make('slug')
                                            ->label(__('resources.post.slug'))
                                            ->required()
                                            ->maxLength(255),
                                    ]),

                                Select::make('author_id')
                                    ->label(__('resources.post.author'))
                                    ->relationship('author', 'name')
                                    ->default(fn () => auth()->id())
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Section::make(__('resources.post.sections.image'))
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('featured_image')
                                    ->label(__('resources.post.featured_image'))
                                    ->collection('featured_image')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                                    ->maxSize(5120)
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '16:9',
                                        '4:3',
                                        '1:1',
                                    ]),
                            ]),
                    ]),

                // SEO en bas, pleine largeur
                Section::make(__('resources.post.sections.seo'))
                    ->columnSpanFull()
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')
                            ->label(__('resources.post.meta_title'))
                            ->maxLength(70)
                            ->helperText(__('resources.post.helpers.meta_title')),

                        Textarea::make('meta_description')
                            ->label(__('resources.post.meta_description'))
                            ->rows(2)
                            ->maxLength(320)
                            ->helperText(__('resources.post.helpers.meta_description')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('featured_image')
                    ->label('')
                    ->collection('featured_image')
                    ->conversion('thumbnail')
                    ->circular(),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('resources.post.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('resources.post.category'))
                    ->badge()
                    ->color(fn ($record) => $record->category?->color ? 'gray' : 'primary')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('resources.post.is_featured_short'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('resources.post.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => __('resources.post.statuses.draft'),
                        'published' => __('resources.post.statuses.published'),
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('resources.post.published_at_short'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('author.name')
                    ->label(__('resources.post.author'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('reading_time')
                    ->label(__('resources.post.reading_time'))
                    ->suffix(' min')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('resources.post.status'))
                    ->options([
                        'draft' => __('resources.post.statuses.draft'),
                        'published' => __('resources.post.statuses.published'),
                    ]),

                Tables\Filters\SelectFilter::make('category')
                    ->label(__('resources.post.category'))
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('resources.post.filters.featured')),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }
}
