<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Filament\Resources\QuoteResource\RelationManagers;
use App\Models\Quote;
use App\Models\Company;
use App\Models\CatalogItem;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use App\Filament\Traits\StandardTableConfig;

class QuoteResource extends Resource
{
    use StandardTableConfig;

    protected static ?string $model = Quote::class;

    protected static ?string $recordTitleAttribute = 'number';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.billing');
    }

    public static function getModelLabel(): string
    {
        return __('resources.quote.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.quote.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->components([
                        Section::make(__('resources.quote.sections.quote_information'))
                            ->components([
                                Forms\Components\TextInput::make('number')
                                    ->label(__('resources.quote.number'))
                                    ->default(fn () => Company::first()?->generateQuoteNumber() ?? 'D-' . date('Y') . '-0001')
                                    ->disabled(fn (?Quote $record): bool => $record !== null) // Editable on create, read-only on edit
                                    ->dehydrated()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->helperText(fn (?Quote $record): ?string =>
                                        $record !== null
                                            ? __('resources.quote.helpers.number_not_editable')
                                            : __('resources.quote.helpers.number_editable')
                                    ),

                                Forms\Components\Select::make('client_id')
                                    ->label(__('resources.quote.client'))
                                    ->relationship('client', 'company_name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $set('project_id', null);
                                    })
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('company_name')
                                            ->label(__('resources.client.company_name'))
                                            ->required(),
                                        Forms\Components\TextInput::make('email')
                                            ->label(__('resources.common.email'))
                                            ->email(),
                                    ]),

                                Forms\Components\Select::make('project_id')
                                    ->label(__('resources.quote.project'))
                                    ->relationship('project', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn (Get $get): bool => !$get('client_id'))
                                    ->options(function (Get $get) {
                                        $clientId = $get('client_id');

                                        if (!$clientId) {
                                            return Project::query()->pluck('name', 'id');
                                        }

                                        return Project::query()
                                            ->where('client_id', $clientId)
                                            ->pluck('name', 'id');
                                    })
                                    ->helperText(fn (Get $get): ?string =>
                                        !$get('client_id')
                                            ? __('resources.quote.helpers.select_client_first')
                                            : null
                                    ),

                                Forms\Components\TextInput::make('subject')
                                    ->label(__('resources.quote.subject'))
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Grid::make(2)
                                    ->components([
                                        Forms\Components\DatePicker::make('issue_date')
                                            ->label(__('resources.quote.issue_date'))
                                            ->default(now())
                                            ->required(),
                                        Forms\Components\DatePicker::make('validity_date')
                                            ->label(__('resources.quote.validity_date'))
                                            ->default(now()->addDays(30)),
                                    ]),

                                Forms\Components\Select::make('status')
                                    ->label(__('resources.quote.status'))
                                    ->options(Quote::getStatusOptions())
                                    ->default('draft')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ])
                            ->columns(2),

                        Section::make(__('resources.quote.sections.introduction'))
                            ->components([
                                Forms\Components\RichEditor::make('introduction')
                                    ->label('')
                                    ->placeholder(__('resources.quote.placeholders.introduction'))
                                    ->fileAttachmentsDisk('local')
                                    ->fileAttachmentsDirectory('quote-attachments')
                                    ->fileAttachmentsVisibility('private'),
                            ])
                            ->collapsible(),

                        Section::make(__('resources.quote.sections.quote_lines'))
                            ->components([
                                Forms\Components\Repeater::make('lines')
                                    ->label('')
                                    ->relationship()
                                    ->components([
                                        Forms\Components\Select::make('catalog_item_id')
                                            ->label(__('resources.quote.catalog_item'))
                                            ->options(CatalogItem::where('is_active', true)->pluck('name', 'id'))
                                            ->searchable()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set) {
                                                if ($state) {
                                                    $item = CatalogItem::find($state);
                                                    if ($item) {
                                                        $set('description', $item->name);
                                                        $set('quantity', $item->default_quantity ?? 1);
                                                        $set('unit_price', $item->unit_price);
                                                        $set('unit', $item->unit);
                                                        $set('vat_rate', $item->vat_rate);
                                                    }
                                                }
                                            })
                                            ->columnSpanFull(),

                                        Forms\Components\RichEditor::make('description')
                                            ->label(__('resources.quote.description'))
                                            ->required()
                                            ->toolbarButtons(self::standardToolbar())
                                            ->columnSpanFull(),

                                        // Ligne 2: Champs numériques
                                        Grid::make(12)
                                            ->components([
                                                Forms\Components\TextInput::make('quantity')
                                                    ->label(__('resources.quote.quantity_short'))
                                                    ->numeric()
                                                    ->default(1)
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->columnSpan(2),

                                                Forms\Components\Select::make('unit')
                                                    ->label(__('resources.quote.unit'))
                                                    ->options(CatalogItem::getUnitOptions())
                                                    ->default('unit')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->columnSpan(2),

                                                Forms\Components\TextInput::make('unit_price')
                                                    ->label(__('resources.quote.unit_price'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->columnSpan(3),

                                                Forms\Components\TextInput::make('vat_rate')
                                                    ->label(__('resources.quote.vat_rate'))
                                                    ->numeric()
                                                    ->suffix('%')
                                                    ->default(fn () => Company::first()?->default_vat_rate ?? 20)
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->columnSpan(2),

                                                Forms\Components\Placeholder::make('line_total')
                                                    ->label(__('resources.quote.line_total'))
                                                    ->content(function (Get $get): string {
                                                        $qty = floatval($get('quantity') ?? 0);
                                                        $price = floatval($get('unit_price') ?? 0);
                                                        return number_format($qty * $price, 2, ',', ' ') . ' €';
                                                    })
                                                    ->columnSpan(3),
                                            ]),
                                    ])
                                    ->defaultItems(1)
                                    ->addActionLabel(__('resources.quote.actions.add_line'))
                                    ->reorderable()
                                    ->reorderableWithDragAndDrop()
                                    ->collapsible()
                                    ->cloneable(),
                            ]),

                        Section::make(__('resources.quote.sections.conclusion'))
                            ->components([
                                Forms\Components\RichEditor::make('conclusion')
                                    ->label('')
                                    ->placeholder(__('resources.quote.placeholders.conclusion'))
                                    ->fileAttachmentsDisk('local')
                                    ->fileAttachmentsDirectory('quote-attachments')
                                    ->fileAttachmentsVisibility('private'),
                            ])
                            ->collapsible(),

                        Section::make(__('resources.quote.sections.internal_notes'))
                            ->components([
                                Forms\Components\Textarea::make('notes')
                                    ->label('')
                                    ->placeholder(__('resources.quote.placeholders.notes'))
                                    ->rows(3),
                            ])
                            ->collapsible()
                            ->collapsed(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->components([
                        Section::make(__('resources.quote.sections.totals'))
                            ->components([
                                Forms\Components\Placeholder::make('total_ht_display')
                                    ->label(__('resources.quote.total_ht'))
                                    ->content(fn (?Quote $record): string => $record ? number_format($record->total_ht, 2, ',', ' ') . ' €' : '0,00 €'),

                                Forms\Components\Placeholder::make('total_vat_display')
                                    ->label(__('resources.quote.total_vat'))
                                    ->content(fn (?Quote $record): string => $record ? number_format($record->total_vat, 2, ',', ' ') . ' €' : '0,00 €'),

                                Forms\Components\Placeholder::make('total_ttc_display')
                                    ->label(__('resources.quote.total_ttc'))
                                    ->content(fn (?Quote $record): string => $record ? number_format($record->total_ttc, 2, ',', ' ') . ' €' : '0,00 €'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label(__('resources.quote.number'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.display_name')
                    ->label(__('resources.quote.client'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->label(__('resources.quote.subject'))
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('resources.quote.status'))
                    ->badge()
                    ->color(fn (string $state): string => self::getStatusColor($state))
                    ->formatStateUsing(fn (string $state): string => Quote::getStatusOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('issue_date')
                    ->label(__('resources.quote.date'))
                    ->date(self::DATE_FORMAT)
                    ->sortable(),
                Tables\Columns\TextColumn::make('validity_date')
                    ->label(__('resources.quote.validity'))
                    ->date(self::DATE_FORMAT)
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_ttc')
                    ->label(__('resources.quote.total_ttc'))
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('resources.quote.created_at'))
                    ->date(self::DATE_FORMAT)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('resources.quote.status'))
                    ->options(Quote::getStatusOptions()),
                Tables\Filters\SelectFilter::make('client_id')
                    ->label(__('resources.quote.client'))
                    ->relationship('client', 'company_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                    ->searchable()
                    ->preload(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),
                    Actions\Action::make('convertToInvoice')
                        ->label(__('resources.quote.actions.convert_to_invoice'))
                        ->icon('heroicon-o-document-duplicate')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading(__('resources.quote.modals.convert_heading'))
                        ->modalDescription(__('resources.quote.modals.convert_description'))
                        ->visible(fn (Quote $record): bool => in_array($record->status, ['draft', 'sent']))
                        ->action(function (Quote $record) {
                            $invoice = $record->convertToInvoice();
                            Notification::make()
                                ->title(__('resources.quote.notifications.invoice_created_title'))
                                ->body(__('resources.quote.notifications.invoice_created_body', ['number' => $invoice->number]))
                                ->success()
                                ->send();
                        }),
                    Actions\Action::make('duplicate')
                        ->label(__('resources.quote.actions.duplicate'))
                        ->icon('heroicon-o-document-duplicate')
                        ->action(function (Quote $record) {
                            $company = Company::first();
                            $newQuote = $record->replicate();
                            $newQuote->number = $company->generateQuoteNumber();
                            $newQuote->status = 'draft';
                            $newQuote->issue_date = now();
                            $newQuote->validity_date = now()->addDays(30);
                            $newQuote->accepted_at = null;
                            $newQuote->save();

                            foreach ($record->lines as $line) {
                                $newLine = $line->replicate();
                                $newLine->quote_id = $newQuote->id;
                                $newLine->save();
                            }

                            Notification::make()
                                ->title(__('resources.quote.notifications.quote_duplicated'))
                                ->success()
                                ->send();
                        }),
                    Actions\Action::make('exportPdf')
                        ->label(__('resources.quote.actions.export_pdf'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->action(function (Quote $record) {
                            $service = app(\App\Services\PdfGeneratorService::class);
                            $pdf = $service->generateQuotePdf($record);
                            $filename = $service->getFilename($record, 'quote');

                            return response()->streamDownload(function () use ($pdf) {
                                echo $pdf->output();
                            }, $filename);
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
            'index' => Pages\ListQuotes::route('/'),
            'create' => Pages\CreateQuote::route('/create'),
            'edit' => Pages\EditQuote::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'subject', 'client.company_name', 'client.first_name', 'client.last_name'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            __('resources.quote.client') => $record->client?->display_name ?? '-',
            __('resources.quote.total_ttc') => number_format($record->total_ttc, 2, ',', ' ') . ' €',
            __('resources.quote.status') => Quote::getStatusOptions()[$record->status] ?? $record->status,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['client']);
    }
}
