<?php

namespace App\Http\Requests\Mcp\SocialPost;

use App\Http\Requests\Mcp\BaseToolRequest;

class PublishSocialPostRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:social_posts,id'],
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
