<?php

namespace App\Http\Requests\Mcp\TimeEntry;

use App\Http\Requests\Mcp\BaseToolRequest;

class CreateTimeEntryRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'task_id' => ['required', 'integer', 'exists:tasks,id'],
            'duration' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'date' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'task_id.required' => 'A task is required for the time entry.',
            'task_id.exists' => 'The specified task does not exist.',
            'duration.required' => 'The duration is required.',
            'duration.min' => 'The duration must be at least 1 minute.',
        ];
    }
}
