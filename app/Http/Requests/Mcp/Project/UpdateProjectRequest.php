<?php

namespace App\Http\Requests\Mcp\Project;

use App\Http\Requests\Mcp\BaseToolRequest;

class UpdateProjectRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:projects,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'status' => ['nullable', 'string', 'in:draft,active,completed,archived,cancelled'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The project ID is required.',
            'id.exists' => 'The specified project does not exist.',
            'name.max' => 'The name must not exceed 255 characters.',
            'client_id.exists' => 'The selected client does not exist.',
            'status.in' => 'Invalid project status.',
        ];
    }
}
