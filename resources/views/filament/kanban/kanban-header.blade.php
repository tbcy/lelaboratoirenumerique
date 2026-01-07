<div
    class="flex items-center justify-between p-3 border-b border-gray-200 dark:border-gray-700"
    style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; border-bottom: 1px solid rgb(229 231 235);"
>
    <div style="display: flex; align-items: center; gap: 0.5rem;">
        <h3 style="font-size: 0.875rem; font-weight: 600; color: rgb(17 24 39);">
            {{ $status['title'] }}
        </h3>
        <span style="display: inline-flex; align-items: center; justify-content: center; width: 1.25rem; height: 1.25rem; font-size: 0.75rem; font-weight: 500; color: rgb(75 85 99); background-color: rgb(229 231 235); border-radius: 9999px;">
            {{ count($status['records']) }}
        </span>
    </div>
</div>
