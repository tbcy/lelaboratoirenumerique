<?php

namespace App\Http\Requests\Mcp\SocialPost;

use App\Http\Requests\Mcp\BaseToolRequest;

class UpdateSocialPostRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:social_posts,id'],
            'content' => ['nullable', 'string', 'max:5000'],
            'connection_ids' => ['nullable', 'array'],
            'connection_ids.*' => ['integer', 'exists:social_connections,id'],
            'status' => ['nullable', 'string', 'in:draft,scheduled,approved'],
            'scheduled_at' => ['nullable', 'date'],
            'media_ids' => ['nullable', 'array'],
            'media_ids.*' => ['integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The post ID is required.',
            'id.exists' => 'The specified post does not exist.',
            'content.max' => 'The content must not exceed 5000 characters.',
            'connection_ids.*.exists' => 'One or more selected connections do not exist.',
            'status.in' => 'The status must be draft, scheduled, or approved.',
        ];
    }
}
