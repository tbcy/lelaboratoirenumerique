<div
    class="p-3 bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 cursor-pointer hover:shadow-md transition-shadow kanban-record"
    data-id="{{ $record->getKey() }}"
    wire:click="recordClicked('{{ $record->getKey() }}', {{ json_encode($record->only(['id', 'title', 'description', 'status', 'priority', 'due_date', 'estimated_minutes', 'client_id', 'project_id'])) }})"
>
    {{-- Header with priority badge --}}
    <div class="flex items-start justify-between gap-2 mb-2">
        <p class="text-sm font-medium text-gray-900 dark:text-white line-clamp-2">
            {{ $record->title }}
        </p>
        @if ($record->priority)
            @php
                $priorityColors = [
                    'low' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                    'medium' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                    'high' => 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300',
                    'urgent' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                ];
            @endphp
            <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium rounded {{ $priorityColors[$record->priority] ?? $priorityColors['medium'] }}">
                {{ ucfirst($record->priority) }}
            </span>
        @endif
    </div>

    {{-- Project & Client info --}}
    @if ($record->project || $record->client)
        <div class="flex items-center gap-1 mb-2 text-xs text-gray-500 dark:text-gray-400">
            @if ($record->client)
                <span class="truncate max-w-[100px]">{{ $record->client->display_name }}</span>
            @endif
            @if ($record->project && $record->client)
                <span>&bull;</span>
            @endif
            @if ($record->project)
                <span class="truncate max-w-[100px]">{{ $record->project->name }}</span>
            @endif
        </div>
    @endif

    {{-- Footer with metadata --}}
    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
        <div class="flex items-center gap-2">
            {{-- Due date --}}
            @if ($record->due_date)
                @php
                    $isOverdue = $record->status->value !== 'done' && $record->due_date->isPast();
                @endphp
                <span class="flex items-center gap-1 {{ $isOverdue ? 'text-red-600 dark:text-red-400' : '' }}">
                    <x-heroicon-m-calendar class="w-3.5 h-3.5" />
                    {{ $record->due_date->format('d/m') }}
                </span>
            @endif

            {{-- Subtasks count --}}
            @if ($record->subtasks->count() > 0)
                @php
                    $completedSubtasks = $record->subtasks->where('status.value', 'done')->count();
                    $totalSubtasks = $record->subtasks->count();
                @endphp
                <span class="flex items-center gap-1">
                    <x-heroicon-m-list-bullet class="w-3.5 h-3.5" />
                    {{ $completedSubtasks }}/{{ $totalSubtasks }}
                </span>
            @endif
        </div>

        {{-- Timer button --}}
        @if ($record->catalog_item_id)
            <button
                type="button"
                wire:click.stop="startTaskTimer({{ $record->getKey() }})"
                class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 hover:text-primary-500 transition-colors"
                title="{{ __('resources.kanban.actions.start_timer') }}"
            >
                <x-heroicon-m-play class="w-4 h-4" />
            </button>
        @endif
    </div>
</div>
