<div
    class="p-3 bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 cursor-pointer hover:shadow-md transition-shadow kanban-record"
    data-id="{{ $record->getKey() }}"
    wire:click="recordClicked('{{ $record->getKey() }}', {{ json_encode($record->toArray()) }})"
>
    <p class="text-sm font-medium text-gray-900 dark:text-white">
        {{ $record->title ?? $record->name ?? 'Untitled' }}
    </p>
</div>
