<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NoteResource\Pages;
use App\Models\Note;
use App\Models\NoteScope;
use Filament\Forms;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use App\Filament\Traits\StandardTableConfig;

class NoteResource extends Resource
{
    use StandardTableConfig;

    protected static ?string $model = Note::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.notes');
    }

    public static function getModelLabel(): string
    {
        return __('resources.note.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.note.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->components([
                        Section::make(__('resources.note.sections.content'))
                            ->components([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('resources.note.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\DateTimePicker::make('datetime')
                                    ->label(__('resources.note.datetime'))
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i'),

                                Forms\Components\RichEditor::make('short_summary')
                                    ->label(__('resources.note.short_summary'))
                                    ->toolbarButtons(self::standardToolbar())
                                    ->columnSpanFull(),
                            ]),

                        Tabs::make('content_tabs')
                            ->tabs([
                                Tabs\Tab::make(__('resources.note.long_summary'))
                                    ->icon('heroicon-o-document-text')
                                    ->schema([
                                        Forms\Components\RichEditor::make('long_summary')
                                            ->label('')
                                            ->toolbarButtons(self::fullToolbar())
                                            ->columnSpanFull(),
                                    ]),

                                Tabs\Tab::make(__('resources.note.notes'))
                                    ->icon('heroicon-o-pencil-square')
                                    ->schema([
                                        Forms\Components\RichEditor::make('notes')
                                            ->label('')
                                            ->toolbarButtons(self::fullToolbar())
                                            ->columnSpanFull(),
                                    ]),

                                Tabs\Tab::make(__('resources.note.transcription'))
                                    ->icon('heroicon-o-microphone')
                                    ->schema([
                                        Forms\Components\Textarea::make('transcription')
                                            ->label('')
                                            ->rows(15)
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->components([
                        Section::make(__('resources.note.sections.organization'))
                            ->components([
                                Forms\Components\Select::make('parent_id')
                                    ->label(__('resources.note.parent'))
                                    ->relationship('parent', 'name', fn (Builder $query, ?Note $record) =>
                                        $query->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Aucune (page racine)'),

                                Forms\Components\Select::make('stakeholders')
                                    ->label(__('resources.note.participants'))
                                    ->relationship('stakeholders', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label(__('resources.stakeholder.name'))
                                            ->required(),
                                        Forms\Components\TextInput::make('email')
                                            ->label(__('resources.stakeholder.email'))
                                            ->email(),
                                        Forms\Components\TextInput::make('company')
                                            ->label(__('resources.stakeholder.company')),
                                    ]),

                                Forms\Components\Select::make('scopes')
                                    ->label(__('resources.note.scopes'))
                                    ->relationship('scopes', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label(__('resources.note_scope.name'))
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set, ?string $state) =>
                                                $set('slug', Str::slug($state))),
                                        Forms\Components\TextInput::make('slug')
                                            ->label(__('resources.note_scope.slug'))
                                            ->required(),
                                        Forms\Components\ColorPicker::make('color')
                                            ->label(__('resources.note_scope.color')),
                                    ]),
                            ]),

                        Section::make()
                            ->components([
                                Forms\Components\Placeholder::make('created_at')
                                    ->label(__('resources.note.created_at'))
                                    ->content(fn (?Note $record): string =>
                                        $record?->created_at?->format(self::DATETIME_FORMAT) ?? '-'
                                    ),

                                Forms\Components\Placeholder::make('children_count')
                                    ->label(__('resources.note.children_count'))
                                    ->content(fn (?Note $record): string =>
                                        $record ? (string) $record->children()->count() : '0'
                                    ),
                            ])
                            ->visible(fn (?Note $record): bool => $record !== null),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('resources.note.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->description(fn (Note $record): ?string =>
                        $record->parent ? "↳ {$record->parent->name}" : null
                    ),

                Tables\Columns\TextColumn::make('datetime')
                    ->label(__('resources.note.datetime'))
                    ->dateTime(self::DATETIME_FORMAT)
                    ->sortable(),

                Tables\Columns\TextColumn::make('stakeholders.name')
                    ->label(__('resources.note.participants'))
                    ->badge()
                    ->limit(3)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('scopes.name')
                    ->label(__('resources.note.scopes'))
                    ->badge()
                    ->color('primary')
                    ->limit(3)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('children_count')
                    ->label(__('resources.note.children_count'))
                    ->counts('children')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('resources.note.created_at'))
                    ->date(self::DATE_FORMAT)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('scopes')
                    ->label(__('resources.note.filters.scope'))
                    ->relationship('scopes', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('has_parent')
                    ->label(__('resources.note.filters.has_parent'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('parent_id'),
                        false: fn (Builder $query) => $query->whereNull('parent_id'),
                    ),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\EditAction::make(),

                    Actions\Action::make('create_subpage')
                        ->label(__('resources.note.actions.create_subpage'))
                        ->icon('heroicon-o-document-plus')
                        ->url(fn (Note $record): string =>
                            static::getUrl('create', ['parent_id' => $record->id])
                        ),

                    Actions\Action::make('duplicate')
                        ->label(__('resources.note.actions.duplicate'))
                        ->icon('heroicon-o-document-duplicate')
                        ->action(function (Note $record) {
                            $newNote = $record->replicate();
                            $newNote->name = $record->name . ' (copie)';
                            $newNote->save();

                            $newNote->stakeholders()->sync($record->stakeholders->pluck('id'));
                            $newNote->scopes()->sync($record->scopes->pluck('id'));

                            Notification::make()
                                ->title('Note dupliquée')
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
                ]),
            ])
            ->defaultSort('datetime', 'desc')
            ->reorderable('sort_order');
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
            'index' => Pages\ListNotes::route('/'),
            'create' => Pages\CreateNote::route('/create'),
            'edit' => Pages\EditNote::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }
}
