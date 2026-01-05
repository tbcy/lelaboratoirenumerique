<div>
    @if($activeTimer)
        <div
            x-data="{
                elapsed: @entangle('elapsedSeconds'),
                show: true
            }"
            x-init="setInterval(() => { elapsed++; }, 1000)"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="bg-white dark:bg-gray-800 border-2 border-amber-400 rounded-lg px-4 py-3 shadow-xl w-96"
            style="opacity: 1 !important;"
        >
            <div class="flex flex-col gap-3">
                <!-- Header: Titre + Icon -->
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1 min-w-0">
                        <div class="text-xs text-gray-600 dark:text-gray-400">{{ __('resources.widgets.active_timer.active_timer') }}</div>
                        <div class="font-semibold text-gray-900 dark:text-white truncate">
                            {{ $activeTimer->task->title }}
                        </div>
                        @if($activeTimer->task->project)
                            <div class="text-xs text-gray-500">📂 {{ $activeTimer->task->project->name }}</div>
                        @endif
                    </div>
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-600 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                <!-- Timer Display + Bouton Stop -->
                <div class="flex items-center justify-between gap-3">
                    <div class="flex-shrink-0 bg-white dark:bg-gray-800 rounded-lg px-3 py-2 border">
                        <div class="text-xl font-mono font-bold text-amber-600"
                             x-text="
                                Math.floor(elapsed/3600).toString().padStart(2,'0') + ':' +
                                Math.floor((elapsed%3600)/60).toString().padStart(2,'0') + ':' +
                                (elapsed%60).toString().padStart(2,'0')
                             ">
                            {{ sprintf('%02d:%02d:%02d', floor($elapsedSeconds/3600), floor(($elapsedSeconds%3600)/60), $elapsedSeconds%60) }}
                        </div>
                    </div>

                    <button
                        wire:click="stopTimer"
                        class="inline-flex items-center gap-2 px-3 py-2 bg-red-600 hover:bg-red-700 text-gray-900 dark:text-white font-semibold rounded-lg transition-colors text-sm"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd" />
                        </svg>
                        {{ __('resources.widgets.active_timer.stop_button') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
