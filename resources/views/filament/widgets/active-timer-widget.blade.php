<div wire:poll.5s="loadActiveTimer">
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
            style="position: fixed; bottom: 1rem; right: 1rem; z-index: 50; background-color: white; border: 2px solid rgb(251 191 36); border-radius: 0.5rem; padding: 0.75rem 1rem; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1); width: 24rem;"
        >
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <!-- Header: Titre + Icon -->
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem;">
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 0.75rem; color: rgb(75 85 99);">{{ __('resources.widgets.active_timer.active_timer') }}</div>
                        <div style="font-weight: 600; color: rgb(17 24 39); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $activeTimer->task->title }}
                        </div>
                        @if($activeTimer->task->project)
                            <div style="font-size: 0.75rem; color: rgb(107 114 128);">📂 {{ $activeTimer->task->project->name }}</div>
                        @endif
                    </div>
                    <div style="flex-shrink: 0;">
                        <svg style="width: 1.25rem; height: 1.25rem; color: rgb(217 119 6); animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                <!-- Timer Display + Bouton Stop -->
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;">
                    <div style="flex-shrink: 0; background-color: rgb(254 252 232); border-radius: 0.5rem; padding: 0.5rem 0.75rem; border: 1px solid rgb(253 224 71);">
                        <div style="font-size: 1.25rem; font-family: ui-monospace, SFMono-Regular, monospace; font-weight: 700; color: rgb(217 119 6);"
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
                        style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; background-color: rgb(220 38 38); color: white; font-weight: 600; border-radius: 0.5rem; font-size: 0.875rem; border: none; cursor: pointer;"
                        onmouseover="this.style.backgroundColor='rgb(185 28 28)'"
                        onmouseout="this.style.backgroundColor='rgb(220 38 38)'"
                    >
                        <svg style="width: 1rem; height: 1rem;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z" clip-rule="evenodd" />
                        </svg>
                        {{ __('resources.widgets.active_timer.stop_button') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
