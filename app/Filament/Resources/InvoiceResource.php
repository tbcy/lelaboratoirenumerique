<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
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

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('navigation.groups.billing');
    }

    public static function getModelLabel(): string
    {
        return __('resources.invoice.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.invoice.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->components([
                        Section::make(__('resources.invoice.sections.invoice_information'))
                            ->components([
                                Forms\Components\TextInput::make('number')
                                    ->label(__('resources.invoice.number'))
                                    ->default(fn () => Company::first()?->generateInvoiceNumber() ?? 'F-' . date('Y') . '-0001')
                                    ->disabled(fn (?Invoice $record): bool => $record !== null) // Editable on create, read-only on edit
                                    ->dehydrated()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->helperText(fn (?Invoice $record): ?string =>
                                        $record !== null
                                            ? __('resources.invoice.helpers.number_not_editable')
                                            : __('resources.invoice.helpers.number_editable')
                                    ),

                                Forms\Components\Select::make('client_id')
                                    ->label(__('resources.invoice.client'))
                                    ->relationship('client', 'company_name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $set('project_id', null);
                                    }),

                                Forms\Components\Select::make('project_id')
                                    ->label(__('resources.invoice.project'))
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
                                            ? __('resources.invoice.helpers.select_client_first')
                                            : null
                                    ),

                                Forms\Components\Select::make('quote_id')
                                    ->label(__('resources.invoice.quote_source'))
                                    ->relationship('quote', 'number')
                                    ->searchable()
                                    ->preload()
                                    ->disabled(),

                                Forms\Components\TextInput::make('subject')
                                    ->label(__('resources.invoice.subject'))
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Grid::make(2)
                                    ->components([
                                        Forms\Components\DatePicker::make('issue_date')
                                            ->label(__('resources.invoice.issue_date'))
                                            ->default(now())
                                            ->required(),
                                        Forms\Components\DatePicker::make('due_date')
                                            ->label(__('resources.invoice.due_date'))
                                            ->default(fn () => now()->addDays(Company::first()?->default_payment_delay ?? 30))
                                            ->required(),
                                    ]),

                                Forms\Components\Select::make('status')
                                    ->label(__('resources.invoice.status'))
                                    ->options(Invoice::getStatusOptions())
                                    ->default('draft')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ])
                            ->columns(2),

                        Section::make(__('resources.invoice.sections.invoice_lines'))
                            ->components([
                                Forms\Components\Repeater::make('lines')
                                    ->label('')
                                    ->relationship()
                                    ->components([
                                        Forms\Components\Select::make('catalog_item_id')
                                            ->label(__('resources.invoice.catalog_item'))
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
                                            ->label(__('resources.invoice.description'))
                                            ->required()
                                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList'])
                                            ->columnSpanFull(),

                                        // Ligne 2: Champs numériques
                                        Grid::make(12)
                                            ->components([
                                                Forms\Components\TextInput::make('quantity')
                                                    ->label(__('resources.invoice.quantity_short'))
                                                    ->numeric()
                                                    ->default(1)
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->columnSpan(2),

                                                Forms\Components\Select::make('unit')
                                                    ->label(__('resources.invoice.unit'))
                                                    ->options(CatalogItem::getUnitOptions())
                                                    ->default('unit')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->columnSpan(2),

                                                Forms\Components\TextInput::make('unit_price')
                                                    ->label(__('resources.invoice.unit_price'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->columnSpan(3),

                                                Forms\Components\TextInput::make('vat_rate')
                                                    ->label(__('resources.invoice.vat_rate'))
                                                    ->numeric()
                                                    ->suffix('%')
                                                    ->default(fn () => Company::first()?->default_vat_rate ?? 20)
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->columnSpan(2),

                                                Forms\Components\Placeholder::make('line_total')
                                                    ->label(__('resources.invoice.line_total'))
                                                    ->content(function (Get $get): string {
                                                        $qty = floatval($get('quantity') ?? 0);
                                                        $price = floatval($get('unit_price') ?? 0);
                                                        return number_format($qty * $price, 2, ',', ' ') . ' €';
                                                    })
                                                    ->columnSpan(3),
                                            ]),
                                    ])
                                    ->defaultItems(1)
                                    ->addActionLabel(__('resources.invoice.actions.add_line'))
                                    ->reorderable()
                                    ->reorderableWithDragAndDrop()
                                    ->collapsible()
                                    ->cloneable(),
                            ]),

                        Section::make(__('resources.invoice.sections.notes'))
                            ->components([
                                Forms\Components\RichEditor::make('introduction')
                                    ->label(__('resources.invoice.introduction'))
                                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList']),
                                Forms\Components\RichEditor::make('conclusion')
                                    ->label(__('resources.invoice.conclusion'))
                                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList']),
                                Forms\Components\Textarea::make('notes')
                                    ->label(__('resources.invoice.notes'))
                                    ->rows(3),
                            ])
                            ->collapsible()
                            ->collapsed(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Group::make()
                    ->components([
                        Section::make(__('resources.invoice.sections.totals'))
                            ->components([
                                Forms\Components\Placeholder::make('total_ht_display')
                                    ->label(__('resources.invoice.total_ht'))
                                    ->content(fn (?Invoice $record): string => $record ? number_format($record->total_ht, 2, ',', ' ') . ' EUR' : '0,00 EUR'),

                                Forms\Components\Placeholder::make('total_vat_display')
                                    ->label(__('resources.invoice.total_vat'))
                                    ->content(fn (?Invoice $record): string => $record ? number_format($record->total_vat, 2, ',', ' ') . ' EUR' : '0,00 EUR'),

                                Forms\Components\Placeholder::make('total_ttc_display')
                                    ->label(__('resources.invoice.total_ttc'))
                                    ->content(fn (?Invoice $record): string => $record ? number_format($record->total_ttc, 2, ',', ' ') . ' EUR' : '0,00 EUR'),
                            ]),

                        Section::make(__('resources.invoice.sections.payment'))
                            ->components([
                                Forms\Components\TextInput::make('amount_paid')
                                    ->label(__('resources.invoice.amount_paid'))
                                    ->numeric()
                                    ->prefix('EUR')
                                    ->default(0),
                                Forms\Components\DatePicker::make('paid_at')
                                    ->label(__('resources.invoice.payment_date')),
                                Forms\Components\Placeholder::make('amount_due_display')
                                    ->label(__('resources.invoice.amount_due'))
                                    ->content(fn (?Invoice $record): string => $record ? number_format($record->amount_due, 2, ',', ' ') . ' EUR' : '0,00 EUR'),
                            ])
                            ->visible(fn (?Invoice $record): bool => $record !== null),
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
                    ->label(__('resources.invoice.number'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.display_name')
                    ->label(__('resources.invoice.client'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->label(__('resources.invoice.subject'))
                    ->searchable()
                    ->limit(30),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('resources.invoice.status'))
                    ->colors([
                        'secondary' => 'draft',
                        'info' => 'sent',
                        'success' => 'paid',
                        'warning' => 'partial',
                        'danger' => 'overdue',
                        'gray' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => Invoice::getStatusOptions()[$state] ?? $state),
                Tables\Columns\TextColumn::make('issue_date')
                    ->label(__('resources.invoice.date'))
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label(__('resources.invoice.due'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (Invoice $record): string => $record->is_overdue ? 'danger' : 'gray'),
                Tables\Columns\TextColumn::make('total_ttc')
                    ->label(__('resources.invoice.total_ttc'))
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_due')
                    ->label(__('resources.invoice.amount_remaining'))
                    ->money('EUR')
                    ->sortable()
                    ->color(fn ($state): string => $state > 0 ? 'warning' : 'success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('resources.invoice.created_at'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('resources.invoice.status'))
                    ->options(Invoice::getStatusOptions()),
                Tables\Filters\SelectFilter::make('client_id')
                    ->label(__('resources.invoice.client'))
                    ->relationship('client', 'company_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('overdue')
                    ->label(__('resources.invoice.filters.overdue'))
                    ->query(fn (Builder $query): Builder => $query->where('status', '!=', 'paid')->where('status', '!=', 'cancelled')->where('due_date', '<', now())),
                Tables\Filters\Filter::make('unpaid')
                    ->label(__('resources.invoice.filters.unpaid'))
                    ->query(fn (Builder $query): Builder => $query->whereNotIn('status', ['paid', 'cancelled'])),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),
                    Actions\Action::make('markAsPaid')
                        ->label(__('resources.invoice.actions.mark_as_paid'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Invoice $record): bool => !in_array($record->status, ['paid', 'cancelled']))
                        ->action(function (Invoice $record) {
                            $record->markAsPaid();
                            Notification::make()
                                ->title(__('resources.invoice.notifications.marked_as_paid'))
                                ->success()
                                ->send();
                        }),
                    Actions\Action::make('sendReminder')
                        ->label(__('resources.invoice.actions.send_reminder'))
                        ->icon('heroicon-o-envelope')
                        ->color('warning')
                        ->visible(fn (Invoice $record): bool => $record->is_overdue),
                    Actions\Action::make('duplicate')
                        ->label(__('resources.invoice.actions.duplicate'))
                        ->icon('heroicon-o-document-duplicate')
                        ->action(function (Invoice $record) {
                            $company = Company::first();
                            $newInvoice = $record->replicate();
                            $newInvoice->number = $company->generateInvoiceNumber();
                            $newInvoice->status = 'draft';
                            $newInvoice->issue_date = now();
                            $newInvoice->due_date = now()->addDays($company->default_payment_delay);
                            $newInvoice->amount_paid = 0;
                            $newInvoice->paid_at = null;
                            $newInvoice->quote_id = null;
                            $newInvoice->save();

                            foreach ($record->lines as $line) {
                                $newLine = $line->replicate();
                                $newLine->invoice_id = $newInvoice->id;
                                $newLine->save();
                            }

                            Notification::make()
                                ->title(__('resources.invoice.notifications.duplicated'))
                                ->success()
                                ->send();
                        }),
                    Actions\Action::make('exportPdf')
                        ->label(__('resources.invoice.actions.export_pdf'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('gray')
                        ->action(function (Invoice $record) {
                            $service = app(\App\Services\PdfGeneratorService::class);
                            $pdf = $service->generateInvoicePdf($record);
                            $filename = $service->getFilename($record, 'invoice');

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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
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
        return static::getModel()::whereNotIn('status', ['paid', 'cancelled'])->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
