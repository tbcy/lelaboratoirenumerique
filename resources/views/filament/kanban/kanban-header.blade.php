<div class="flex items-center justify-between p-3 border-b border-gray-200 dark:border-gray-700">
    <div class="flex items-center gap-2">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
            {{ $status['title'] }}
        </h3>
        <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-medium text-gray-600 bg-gray-200 rounded-full dark:bg-gray-700 dark:text-gray-300">
            {{ count($status['records']) }}
        </span>
    </div>
</div>
