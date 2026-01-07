<x-filament-panels::page>
    <div
        x-data="kanbanBoard({
            statuses: @js($statuses),
        })"
        class="flex gap-4 overflow-x-auto pb-4"
    >
        @foreach ($statuses as $status)
            @include($statusView, [
                'status' => $status,
                'headerView' => $headerView,
                'recordView' => $recordView,
            ])
        @endforeach
    </div>

    @include($scriptsView)

    @unless ($disableEditModal)
        <x-filament::modal
            id="kanban--edit-record-modal"
            :heading="$this->getEditModalTitle()"
            :slide-over="$this->getEditModalSlideOver()"
            :width="$this->getEditModalWidth()"
        >
            <x-filament-panels::form wire:submit="editModalFormSubmitted">
                {{ $this->form }}

                <x-filament-panels::form.actions
                    :actions="[
                        \Filament\Actions\Action::make('save')
                            ->label($this->getEditModalSaveButtonLabel())
                            ->submit('editModalFormSubmitted'),
                        \Filament\Actions\Action::make('cancel')
                            ->label($this->getEditModalCancelButtonLabel())
                            ->color('gray')
                            ->close(),
                    ]"
                />
            </x-filament-panels::form>
        </x-filament::modal>
    @endunless
</x-filament-panels::page>
