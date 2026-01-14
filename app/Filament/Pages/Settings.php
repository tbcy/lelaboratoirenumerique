<?php

namespace App\Filament\Pages;

use App\Models\Company;
use App\Services\DalleImageService;
use Filament\Forms;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\Alignment;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 100;

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('resources.settings.navigation_label');
    }

    public function getTitle(): string
    {
        return __('resources.settings.page_title');
    }

    public function mount(): void
    {
        $company = Company::first();

        if ($company) {
            $this->form->fill($company->toArray());
        } else {
            $this->form->fill([
                'country' => 'France',
                'quote_prefix' => 'D-',
                'quote_counter' => 1,
                'invoice_prefix' => 'F-',
                'invoice_counter' => 1,
                'default_payment_delay' => 30,
                'default_vat_rate' => 20.00,
                'google_location' => 'europe-west1',
            ]);
        }
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    protected function getFormContentComponent(): Form
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment()),
            ]);
    }

    public function getFormActionsAlignment(): string|Alignment
    {
        return Alignment::Start;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        Tabs\Tab::make(__('resources.settings.tabs.company'))
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Section::make(__('resources.settings.sections.general_information'))
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label(__('resources.settings.fields.company_name'))
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('legal_form')
                                            ->label(__('resources.settings.fields.legal_form'))
                                            ->placeholder(__('resources.settings.fields.legal_form_placeholder'))
                                            ->maxLength(50),
                                        Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('siret')
                                                    ->label(__('resources.settings.fields.siret'))
                                                    ->maxLength(14),
                                                Forms\Components\TextInput::make('vat_number')
                                                    ->label(__('resources.settings.fields.vat_number'))
                                                    ->maxLength(255),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('email')
                                                    ->label(__('resources.settings.fields.email'))
                                                    ->email()
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('phone')
                                                    ->label(__('resources.settings.fields.phone'))
                                                    ->tel()
                                                    ->maxLength(255),
                                            ]),
                                        Forms\Components\TextInput::make('website')
                                            ->label(__('resources.settings.fields.website'))
                                            ->url()
                                            ->maxLength(255),
                                        Forms\Components\FileUpload::make('logo')
                                            ->label(__('resources.settings.fields.logo'))
                                            ->image()
                                            ->directory('logos')
                                            ->maxSize(1024),
                                    ])
                                    ->columns(2),

                                Section::make(__('resources.settings.sections.address'))
                                    ->schema([
                                        Forms\Components\TextInput::make('address')
                                            ->label(__('resources.settings.fields.address'))
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('address_2')
                                            ->label(__('resources.settings.fields.address_complement'))
                                            ->maxLength(255),
                                        Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('postal_code')
                                                    ->label(__('resources.settings.fields.postal_code'))
                                                    ->maxLength(10),
                                                Forms\Components\TextInput::make('city')
                                                    ->label(__('resources.settings.fields.city'))
                                                    ->maxLength(255),
                                                Forms\Components\TextInput::make('country')
                                                    ->label(__('resources.settings.fields.country'))
                                                    ->default('France')
                                                    ->maxLength(255),
                                            ]),
                                    ]),

                                Section::make(__('resources.settings.sections.bank_details'))
                                    ->schema([
                                        Forms\Components\TextInput::make('bank_name')
                                            ->label(__('resources.settings.fields.bank_name'))
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('iban')
                                            ->label(__('resources.settings.fields.iban'))
                                            ->maxLength(34),
                                        Forms\Components\TextInput::make('bic')
                                            ->label(__('resources.settings.fields.bic'))
                                            ->maxLength(11),
                                    ])
                                    ->columns(3)
                                    ->collapsible(),
                            ]),

                        Tabs\Tab::make(__('resources.settings.tabs.billing'))
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make(__('resources.settings.sections.numbering'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('quote_prefix')
                                                    ->label(__('resources.settings.fields.quote_prefix'))
                                                    ->default('D-')
                                                    ->maxLength(10),
                                                Forms\Components\TextInput::make('quote_counter')
                                                    ->label(__('resources.settings.fields.quote_counter'))
                                                    ->numeric()
                                                    ->default(1),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('invoice_prefix')
                                                    ->label(__('resources.settings.fields.invoice_prefix'))
                                                    ->default('F-')
                                                    ->maxLength(10),
                                                Forms\Components\TextInput::make('invoice_counter')
                                                    ->label(__('resources.settings.fields.invoice_counter'))
                                                    ->numeric()
                                                    ->default(1),
                                            ]),
                                    ]),

                                Section::make(__('resources.settings.sections.default_settings'))
                                    ->schema([
                                        Forms\Components\TextInput::make('default_payment_delay')
                                            ->label(__('resources.settings.fields.payment_terms_days'))
                                            ->numeric()
                                            ->default(30)
                                            ->suffix(__('resources.settings.fields.payment_terms_suffix')),
                                        Forms\Components\TextInput::make('default_vat_rate')
                                            ->label(__('resources.settings.fields.default_vat_rate'))
                                            ->numeric()
                                            ->default(20)
                                            ->suffix(__('resources.settings.fields.vat_suffix')),
                                    ])
                                    ->columns(2),

                                Section::make(__('resources.settings.sections.legal_mentions'))
                                    ->schema([
                                        Forms\Components\Textarea::make('legal_mentions')
                                            ->label(__('resources.settings.fields.legal_notice'))
                                            ->rows(5)
                                            ->placeholder(__('resources.settings.fields.legal_notice_placeholder')),
                                    ]),
                            ]),

                        Tabs\Tab::make(__('resources.settings.tabs.integrations'))
                            ->icon('heroicon-o-puzzle-piece')
                            ->schema([
                                Section::make(__('resources.settings.sections.openai_dalle'))
                                    ->description(__('resources.settings.sections.openai_dalle_description'))
                                    ->schema([
                                        Forms\Components\TextInput::make('openai_api_key')
                                            ->label(__('resources.settings.fields.openai_api_key'))
                                            ->password()
                                            ->revealable()
                                            ->helperText(__('resources.settings.fields.openai_api_key_help')),

                                        Forms\Components\Select::make('openai_chat_model')
                                            ->label(__('resources.settings.fields.openai_chat_model'))
                                            ->options([
                                                'gpt-4o' => 'GPT-4o (Recommandé)',
                                                'gpt-4o-mini' => 'GPT-4o Mini (Économique)',
                                                'gpt-4-turbo' => 'GPT-4 Turbo',
                                                'gpt-4' => 'GPT-4',
                                                'o1' => 'o1 (Raisonnement)',
                                                'o1-mini' => 'o1 Mini',
                                            ])
                                            ->default('gpt-4o')
                                            ->helperText(__('resources.settings.fields.openai_chat_model_help')),

                                        Forms\Components\Select::make('summary_detail_level')
                                            ->label(__('resources.settings.fields.summary_detail_level'))
                                            ->options([
                                                'concise' => __('resources.settings.fields.summary_detail_level_concise'),
                                                'exhaustive' => __('resources.settings.fields.summary_detail_level_exhaustive'),
                                            ])
                                            ->default('concise')
                                            ->helperText(__('resources.settings.fields.summary_detail_level_help')),

                                        Forms\Components\Textarea::make('image_generation_prompt')
                                            ->label(__('resources.settings.fields.image_generation_prompt'))
                                            ->rows(12)
                                            ->placeholder(DalleImageService::getDefaultSystemPrompt())
                                            ->helperText(__('resources.settings.fields.image_generation_prompt_help')),

                                        Forms\Components\Placeholder::make('default_prompt_info')
                                            ->label(__('resources.settings.fields.default_prompt_label'))
                                            ->content(__('resources.settings.fields.default_prompt_content')),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $company = Company::first();

        if ($company) {
            $company->update($data);
        } else {
            Company::create($data);
        }

        Notification::make()
            ->title(__('resources.settings.notifications.saved'))
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('resources.settings.actions.save'))
                ->submit('save'),
        ];
    }
}
