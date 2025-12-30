<?php

namespace App\Http\Requests\Mcp\Post;

use App\Http\Requests\Mcp\BaseToolRequest;

class PublishPostRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:posts,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The post ID is required.',
            'id.exists' => 'The specified post does not exist.',
        ];
    }
}
