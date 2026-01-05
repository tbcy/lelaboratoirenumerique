<?php

namespace App\Http\Requests\Mcp\Project;

use App\Http\Requests\Mcp\BaseToolRequest;

class CreateProjectRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'status' => ['nullable', 'string', 'in:draft,active,completed,archived,cancelled'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The project name is required.',
            'name.max' => 'The name must not exceed 255 characters.',
            'client_id.exists' => 'The selected client does not exist.',
            'status.in' => 'Invalid project status.',
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
        ];
    }
}
