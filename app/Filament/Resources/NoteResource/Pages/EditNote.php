<?php

namespace App\Filament\Resources\NoteResource\Pages;

use App\Filament\Resources\NoteResource;
use App\Services\AiSummaryService;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditNote extends EditRecord
{
    protected static string $resource = NoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generateSummary')
                ->label(__('resources.note.actions.generate_summary'))
                ->icon('heroicon-o-sparkles')
                ->color('info')
                ->modalHeading(__('resources.note.modals.generate_summary_heading'))
                ->modalDescription(__('resources.note.modals.generate_summary_description'))
                ->modalSubmitActionLabel(__('resources.note.actions.generate'))
                ->form([
                    Select::make('detail_level')
                        ->label(__('resources.note.form.detail_level'))
                        ->options([
                            'concise' => __('resources.settings.fields.summary_detail_level_concise'),
                            'exhaustive' => __('resources.settings.fields.summary_detail_level_exhaustive'),
                        ])
                        ->default('concise')
                        ->required()
                        ->helperText(__('resources.settings.fields.summary_detail_level_help')),
                ])
                ->visible(fn () => ! empty($this->record->notes) || ! empty($this->record->transcription))
                ->action(function (array $data) {
                    $service = app(AiSummaryService::class);

                    if (! $service->isConfigured()) {
                        Notification::make()
                            ->title(__('resources.note.notifications.openai_not_configured'))
                            ->danger()
                            ->send();

                        return;
                    }

                    $result = $service->generateSummaries(
                        $this->record->notes ?? '',
                        $this->record->transcription ?? '',
                        $data['detail_level']
                    );

                    if ($result['success']) {
                        $this->record->update([
                            'short_summary' => $result['short_summary'],
                            'long_summary' => $result['long_summary'],
                        ]);

                        $this->refreshFormData(['short_summary', 'long_summary']);

                        Notification::make()
                            ->title(__('resources.note.notifications.summary_generated'))
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title($result['error'])
                            ->danger()
                            ->send();
                    }
                }),
            Actions\Action::make('create_subpage')
                ->label(__('resources.note.actions.create_subpage'))
                ->icon('heroicon-o-document-plus')
                ->url(fn () => NoteResource::getUrl('create', ['parent_id' => $this->record->id])),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }
}
