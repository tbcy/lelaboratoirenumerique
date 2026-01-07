<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Traits\StandardTableConfig;

class ProjectResource extends Resource
{
    use StandardTableConfig;

    protected static ?string $model = Project::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-folder';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.projects');
    }

    public static function getModelLabel(): string
    {
        return __('resources.project.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.project.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->components([
                        Section::make(__('resources.project.sections.project_information'))
                            ->components([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('resources.project.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\RichEditor::make('description')
                                    ->label(__('resources.project.description'))
                                    ->toolbarButtons(self::standardToolbar())
                                    ->columnSpanFull(),

                                Forms\Components\Select::make('client_id')
                                    ->label(__('resources.project.client'))
                                    ->relationship('client', 'company_name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\Select::make('type')
                                            ->label(__('resources.client.type'))
                                            ->options([
                                                'company' => __('enums.client_type.company'),
                                                'individual' => __('enums.client_type.individual'),
                                            ])
                                            ->default('company')
                                            ->required(),
                                        Forms\Components\TextInput::make('company_name')
                                            ->label(__('resources.common.name'))
                                            ->required(),
                                        Forms\Components\TextInput::make('email')
                                            ->label(__('resources.common.email'))
                                            ->email(),
                                    ]),

                                Forms\Components\Select::make('status')
                                    ->label(__('resources.project.status'))
                                    ->options(Project::getStatusOptions())
                                    ->default('draft')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ])
                            ->columns(2),

                        Section::make(__('resources.project.sections.planning'))
                            ->components([
                                Grid::make(2)
                                    ->components([
                                        Forms\Components\DatePicker::make('start_date')
                                            ->label(__('resources.project.start_date')),

                                        Forms\Components\DatePicker::make('end_date')
                                            ->label(__('resources.project.end_date'))
                                            ->after('start_date'),
                                    ]),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->components([
                        Section::make(__('resources.project.sections.budget_time'))
                            ->components([
                                Forms\Components\Placeholder::make('budget')
                                    ->label(__('resources.project.budget'))
                                    ->content(fn (?Project $record): string =>
                                        $record ? number_format($record->budget, 2, ',', ' ') . ' EUR' : '0,00 EUR'
                                    )
                                    ->helperText('Calculé automatiquement depuis les devis acceptés'),

                                Forms\Components\Placeholder::make('estimated_hours')
                                    ->label(__('resources.project.estimated_hours'))
                                    ->content(fn (?Project $record): string =>
                                        $record ? $record->estimated_hours . ' h' : '0 h'
                                    )
                                    ->helperText('Calculé automatiquement depuis les devis acceptés'),

                                Forms\Components\Placeholder::make('logged_hours')
                                    ->label(__('resources.project.logged_hours'))
                                    ->content(function (?Project $record): string {
                                        if (!$record) {
                                            return '0h';
                                        }
                                        return number_format($record->total_logged_hours, 1) . 'h';
                                    }),

                                Forms\Components\Placeholder::make('progress')
                                    ->label(__('resources.project.progress'))
                                    ->content(function (?Project $record): string {
                                        if (!$record || !$record->estimated_hours) {
                                            return __('resources.project.placeholders.na');
                                        }
                                        $percent = min(100, round(($record->total_logged_hours / $record->estimated_hours) * 100));
                                        return "{$percent}%";
                                    }),
                            ]),

                        Section::make(__('resources.project.sections.customization'))
                            ->components([
                                Forms\Components\ColorPicker::make('color')
                                    ->label(__('resources.project.color')),
                            ])
                            ->collapsible()
                            ->collapsed(),

                        Section::make(__('resources.project.sections.statistics'))
                            ->components([
                                Forms\Components\Placeholder::make('tasks_count')
                                    ->label(__('resources.project.tasks_count'))
                                    ->content(fn (?Project $record): string =>
                                        $record ? __('resources.project.placeholders.tasks', ['count' => $record->tasks()->count()]) : __('resources.project.placeholders.task_single')
                                    ),

                                Forms\Components\Placeholder::make('quotes_count')
                                    ->label(__('resources.project.quotes_count'))
                                    ->content(fn (?Project $record): string =>
                                        $record ? __('resources.project.placeholders.quotes', ['count' => $record->quotes()->count()]) : __('resources.project.placeholders.quote_single')
                                    ),

                                Forms\Components\Placeholder::make('invoices_count')
                                    ->label(__('resources.project.invoices_count'))
                                    ->content(fn (?Project $record): string =>
                                        $record ? __('resources.project.placeholders.invoices', ['count' => $record->invoices()->count()]) : __('resources.project.placeholders.invoice_single')
                                    ),
                            ])
                            ->visible(fn (?Project $record): bool => $record !== null),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ColorColumn::make('color')
                    ->label('')
                    ->width('20px'),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('resources.project.project'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('client.display_name')
                    ->label(__('resources.project.client'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('resources.project.status'))
                    ->badge()
                    ->color(fn (string $state): string => self::getStatusColor($state))
                    ->formatStateUsing(fn (string $state): string => Project::getStatusOptions()[$state] ?? $state),

                Tables\Columns\TextColumn::make('budget')
                    ->label(__('resources.project.budget'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('estimated_hours')
                    ->label(__('resources.project.estimated_hours'))
                    ->formatStateUsing(fn (?int $state): string => $state ? "{$state}h" : '-')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_logged_hours')
                    ->label(__('resources.project.logged_hours'))
                    ->formatStateUsing(fn (float $state): string => number_format($state, 1) . 'h')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('start_date')
                    ->label(__('resources.project.start'))
                    ->date(self::DATE_FORMAT)
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label(__('resources.project.end'))
                    ->date(self::DATE_FORMAT)
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tasks_count')
                    ->label(__('resources.project.tasks_count'))
                    ->counts('tasks')
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('quotes_count')
                    ->label(__('resources.project.quotes_count'))
                    ->counts('quotes')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('invoices_count')
                    ->label(__('resources.project.invoices_count'))
                    ->counts('invoices')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('resources.project.created_at'))
                    ->date(self::DATE_FORMAT)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('resources.project.status'))
                    ->options(Project::getStatusOptions()),

                Tables\Filters\SelectFilter::make('client_id')
                    ->label(__('resources.project.client'))
                    ->relationship('client', 'company_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('has_budget')
                    ->label(__('resources.project.filters.with_budget'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('budget')->where('budget', '>', 0)),

                Tables\Filters\Filter::make('active_projects')
                    ->label(__('resources.project.filters.active_projects'))
                    ->query(fn (Builder $query): Builder => $query->where('status', 'active')),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),

                    Actions\Action::make('createTask')
                        ->label(__('resources.project.actions.create_task'))
                        ->icon('heroicon-o-plus-circle')
                        ->url(fn (Project $record): string =>
                            TaskResource::getUrl('create', ['project_id' => $record->id])
                        ),

                    Actions\Action::make('createQuote')
                        ->label(__('resources.project.actions.create_quote'))
                        ->icon('heroicon-o-document-text')
                        ->url(fn (Project $record): string =>
                            QuoteResource::getUrl('create', ['project_id' => $record->id])
                        ),

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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TasksRelationManager::class,
            RelationManagers\TimeEntriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
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
        return static::getModel()::where('status', 'active')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'client.company_name', 'client.first_name', 'client.last_name'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            __('resources.project.client') => $record->client?->display_name ?? '-',
            __('resources.project.status') => Project::getStatusOptions()[$record->status] ?? $record->status,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['client']);
    }
}
