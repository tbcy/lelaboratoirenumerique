<div
    class="flex flex-col flex-shrink-0 w-80 bg-gray-100 dark:bg-gray-800 rounded-xl"
    style="display: flex; flex-direction: column; flex-shrink: 0; width: 320px; background-color: rgb(243 244 246); border-radius: 0.75rem;"
    x-data="{ status: @js($status['id']) }"
>
    @include($headerView, ['status' => $status])

    <div
        class="flex flex-col gap-2 p-3 min-h-[200px] kanban-column"
        style="display: flex; flex-direction: column; gap: 0.5rem; padding: 0.75rem; min-height: 200px;"
        data-status="{{ $status['id'] }}"
        x-ref="column-{{ $status['id'] }}"
    >
        @foreach ($status['records'] as $record)
            @include($recordView, ['record' => $record])
        @endforeach
    </div>
</div>
