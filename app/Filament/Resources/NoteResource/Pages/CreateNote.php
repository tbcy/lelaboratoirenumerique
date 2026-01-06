<?php

namespace App\Filament\Resources\NoteResource\Pages;

use App\Filament\Resources\NoteResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\Request;

class CreateNote extends CreateRecord
{
    protected static string $resource = NoteResource::class;

    public function mount(): void
    {
        parent::mount();

        // Handle parent_id from URL query parameter
        $parentId = request()->query('parent_id');
        if ($parentId) {
            $this->form->fill([
                'parent_id' => (int) $parentId,
            ]);
        }
    }
}
