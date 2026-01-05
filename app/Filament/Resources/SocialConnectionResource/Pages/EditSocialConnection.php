<?php

namespace App\Filament\Resources\SocialConnectionResource\Pages;

use App\Filament\Resources\SocialConnectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSocialConnection extends EditRecord
{
    protected static string $resource = SocialConnectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Make credentials visible for the form (they are hidden by default in the model)
        $data['credentials'] = $this->record->credentials;

        return $data;
    }
}
