<?php

namespace App\Filament\Resources\GeneratedImages\Tables;

use App\Models\GeneratedImage;
use Filament\Actions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Filament\Tables\Columns;
use Filament\Tables\Filters;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GeneratedImagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Columns\ImageColumn::make('file_path')
                    ->label(__('resources.generated_image.preview'))
                    ->disk('local')
                    ->visibility('private')
                    ->height(80)
                    ->width(80),

                Columns\TextColumn::make('content_source')
                    ->label(__('resources.generated_image.source_content'))
                    ->limit(60)
                    ->wrap()
                    ->searchable(),

                Columns\TextColumn::make('socialPost.content')
                    ->label(__('resources.generated_image.linked_post'))
                    ->limit(40)
                    ->placeholder(__('resources.generated_image.no_linked_post'))
                    ->toggleable(),

                Columns\TextColumn::make('file_size')
                    ->label(__('resources.generated_image.file_size'))
                    ->formatStateUsing(fn (int $state): string => number_format($state / 1024, 1) . ' KB')
                    ->toggleable(),

                Columns\TextColumn::make('created_at')
                    ->label(__('resources.generated_image.generated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Filters\Filter::make('has_social_post')
                    ->label(__('resources.generated_image.filters.linked_to_post'))
                    ->query(fn ($query) => $query->whereNotNull('social_post_id')),

                Filters\Filter::make('standalone')
                    ->label(__('resources.generated_image.filters.standalone'))
                    ->query(fn ($query) => $query->whereNull('social_post_id')),
            ])
            ->recordActions([
                Actions\ActionGroup::make([
                    Actions\Action::make('download')
                        ->label(__('resources.generated_image.actions.download'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (GeneratedImage $record): StreamedResponse {
                            return Storage::disk('local')->download(
                                $record->file_path,
                                $record->file_name
                            );
                        }),

                    Actions\Action::make('view_prompt')
                        ->label(__('resources.generated_image.actions.view_prompt'))
                        ->icon('heroicon-o-document-text')
                        ->modalHeading(__('resources.generated_image.modals.prompt_heading'))
                        ->modalContent(fn (GeneratedImage $record) => view('filament.modals.view-prompt', ['prompt' => $record->prompt]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel(__('resources.generated_image.actions.close')),

                    Actions\DeleteAction::make()
                        ->before(function (GeneratedImage $record) {
                            // Delete file from storage
                            if (Storage::disk('local')->exists($record->file_path)) {
                                Storage::disk('local')->delete($record->file_path);
                            }
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if (Storage::disk('local')->exists($record->file_path)) {
                                    Storage::disk('local')->delete($record->file_path);
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
