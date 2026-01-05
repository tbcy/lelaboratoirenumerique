<?php

namespace App\Http\Requests\Mcp\Task;

use App\Http\Requests\Mcp\BaseToolRequest;

class UpdateTaskRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:tasks,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:todo,in_progress,done'],
            'priority' => ['nullable', 'string', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
            'estimated_minutes' => ['nullable', 'integer', 'min:0'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'parent_id' => ['nullable', 'integer', 'exists:tasks,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The task ID is required.',
            'id.exists' => 'The specified task does not exist.',
            'title.max' => 'The title must not exceed 255 characters.',
            'status.in' => 'The status must be todo, in_progress, or done.',
            'priority.in' => 'The priority must be low, medium, or high.',
            'project_id.exists' => 'The selected project does not exist.',
            'client_id.exists' => 'The selected client does not exist.',
            'parent_id.exists' => 'The parent task does not exist.',
        ];
    }
}
