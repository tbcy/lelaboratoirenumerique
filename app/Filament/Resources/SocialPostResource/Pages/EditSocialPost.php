<?php

namespace App\Filament\Resources\SocialPostResource\Pages;

use App\Filament\Resources\SocialPostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditSocialPost extends EditRecord
{
    protected static string $resource = SocialPostResource::class;

    public function getHeading(): string
    {
        return Str::limit($this->record->content, 30);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
