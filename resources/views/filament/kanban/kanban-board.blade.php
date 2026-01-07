<x-filament-panels::page>
    <div
        x-data="kanbanBoard({
            statuses: @js($statuses),
        })"
        class="flex gap-4 overflow-x-auto pb-4"
        style="display: flex; flex-direction: row; gap: 1rem; overflow-x: auto; padding-bottom: 1rem;"
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
            <form wire:submit="editModalFormSubmitted" id="kanban-edit-form">
                {{ $this->form }}
            </form>

            <x-slot:footer>
                <x-filament::button
                    color="gray"
                    x-on:click="$dispatch('close-modal', { id: 'kanban--edit-record-modal' })"
                >
                    {{ $this->getEditModalCancelButtonLabel() }}
                </x-filament::button>

                <x-filament::button type="button" wire:click="editModalFormSubmitted">
                    {{ $this->getEditModalSaveButtonLabel() }}
                </x-filament::button>
            </x-slot:footer>
        </x-filament::modal>
    @endunless
</x-filament-panels::page>
