<div
    class="flex flex-col flex-shrink-0 w-80 bg-gray-100 dark:bg-gray-800 rounded-xl"
    x-data="{ status: @js($status['id']) }"
>
    @include($headerView, ['status' => $status])

    <div
        class="flex flex-col gap-2 p-3 min-h-[200px] kanban-column"
        data-status="{{ $status['id'] }}"
        x-ref="column-{{ $status['id'] }}"
    >
        @foreach ($status['records'] as $record)
            @include($recordView, ['record' => $record])
        @endforeach
    </div>
</div>
