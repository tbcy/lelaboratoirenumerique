<div
    class="p-3 bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 cursor-pointer hover:shadow-md transition-shadow kanban-record"
    style="padding: 0.75rem; background-color: white; border-radius: 0.5rem; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); border: 1px solid rgb(229 231 235); cursor: pointer;"
    data-id="{{ $record->getKey() }}"
    wire:click="recordClicked('{{ $record->getKey() }}', {{ json_encode($record->only(['id', 'title', 'description', 'status', 'priority', 'due_date', 'estimated_minutes', 'client_id', 'project_id'])) }})"
>
    {{-- Header with priority badge --}}
    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.5rem;">
        <p style="font-size: 0.875rem; font-weight: 500; color: rgb(17 24 39);">
            {{ $record->title }}
        </p>
        @if ($record->priority)
            @php
                $priorityStyles = [
                    'low' => 'background-color: rgb(243 244 246); color: rgb(75 85 99);',
                    'medium' => 'background-color: rgb(219 234 254); color: rgb(29 78 216);',
                    'high' => 'background-color: rgb(255 237 213); color: rgb(194 65 12);',
                    'urgent' => 'background-color: rgb(254 226 226); color: rgb(185 28 28);',
                ];
            @endphp
            <span style="display: inline-flex; align-items: center; padding: 0.125rem 0.375rem; font-size: 0.75rem; font-weight: 500; border-radius: 0.25rem; {{ $priorityStyles[$record->priority] ?? $priorityStyles['medium'] }}">
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
        <button
            type="button"
            wire:click.stop="startTaskTimer({{ $record->getKey() }})"
            class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 hover:text-primary-500 transition-colors"
            style="padding: 0.25rem; border-radius: 0.25rem; color: rgb(156 163 175); display: inline-flex; align-items: center; justify-content: center;"
            title="{{ __('resources.kanban.actions.start_timer') }}"
        >
            <x-heroicon-m-play class="w-4 h-4" style="width: 1rem; height: 1rem;" />
        </button>
    </div>
</div>
