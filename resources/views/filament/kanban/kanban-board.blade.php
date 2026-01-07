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
            <form wire:submit="editModalFormSubmitted">
                {{ $this->form }}

                <div class="mt-6 flex justify-end gap-x-3">
                    <x-filament::button
                        color="gray"
                        x-on:click="$dispatch('close-modal', { id: 'kanban--edit-record-modal' })"
                    >
                        {{ $this->getEditModalCancelButtonLabel() }}
                    </x-filament::button>

                    <x-filament::button type="submit">
                        {{ $this->getEditModalSaveButtonLabel() }}
                    </x-filament::button>
                </div>
            </form>
        </x-filament::modal>
    @endunless
</x-filament-panels::page>
