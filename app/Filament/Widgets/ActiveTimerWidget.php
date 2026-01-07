<?php

namespace App\Filament\Widgets;

use App\Models\TimeEntry;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class ActiveTimerWidget extends Widget
{
    protected string $view = 'filament.widgets.active-timer-widget';
    protected int | string | array $columnSpan = 'full';

    public ?TimeEntry $activeTimer = null;
    public int $elapsedSeconds = 0;

    public function mount(): void
    {
        $this->loadActiveTimer();
    }

    #[On('timer-started')]
    #[On('timer-stopped')]
    public function loadActiveTimer(): void
    {
        $this->activeTimer = auth()->user()->activeTimer();

        if ($this->activeTimer) {
            $this->elapsedSeconds = $this->activeTimer->getElapsedSeconds();
        } else {
            $this->elapsedSeconds = 0;
        }
    }

    public function updateElapsed(): void
    {
        if ($this->activeTimer) {
            $this->elapsedSeconds = $this->activeTimer->fresh()->getElapsedSeconds();
        }
    }

    public function stopTimer(): void
    {
        if ($this->activeTimer) {
            $elapsedSeconds = $this->activeTimer->getElapsedSeconds();
            $this->activeTimer->stop();
            $this->dispatch('timer-stopped');
            $this->loadActiveTimer();

            \Filament\Notifications\Notification::make()
                ->title(__('resources.widgets.active_timer.notifications.timer_stopped'))
                ->body(__('resources.widgets.active_timer.notifications.time_recorded', ['duration' => $this->formatDuration($elapsedSeconds)]))
                ->success()
                ->send();
        }
    }

    public function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%02d:%02d', $minutes, $secs);
    }
}
