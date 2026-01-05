<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TaskStatus: string implements HasLabel
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case REVIEW = 'review';
    case DONE = 'done';

    public function getLabel(): string
    {
        return match ($this) {
            self::TODO => __('enums.task_status.todo'),
            self::IN_PROGRESS => __('enums.task_status.in_progress'),
            self::REVIEW => __('enums.task_status.review'),
            self::DONE => __('enums.task_status.done'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::TODO => 'gray',
            self::IN_PROGRESS => 'info',
            self::REVIEW => 'warning',
            self::DONE => 'success',
        };
    }

    public static function statuses(): \Illuminate\Support\Collection
    {
        return collect(self::cases())->map(fn ($case) => [
            'id' => $case->value,
            'title' => $case->getLabel(),
        ]);
    }
}
