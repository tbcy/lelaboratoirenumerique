<?php

namespace App\Http\Requests\Mcp\SocialPost;

use App\Http\Requests\Mcp\BaseToolRequest;

class CreateSocialPostRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:5000'],
            'connection_ids' => ['required', 'array', 'min:1'],
            'connection_ids.*' => ['integer', 'exists:social_connections,id'],
            'status' => ['nullable', 'string', 'in:draft,scheduled,approved'],
            'scheduled_at' => ['nullable', 'date', 'required_if:status,scheduled'],
            'media_ids' => ['nullable', 'array'],
            'media_ids.*' => ['integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'The post content is required.',
            'content.max' => 'The content must not exceed 5000 characters.',
            'connection_ids.required' => 'At least one social connection is required.',
            'connection_ids.min' => 'At least one social connection is required.',
            'connection_ids.*.exists' => 'One or more selected connections do not exist.',
            'status.in' => 'The status must be draft, scheduled, or approved.',
            'scheduled_at.required_if' => 'A scheduled date is required when status is scheduled.',
        ];
    }
}
