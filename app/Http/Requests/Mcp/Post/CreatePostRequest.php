<?php

namespace App\Http\Requests\Mcp\Post;

use App\Http\Requests\Mcp\BaseToolRequest;

class CreatePostRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:posts,slug'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'status' => ['nullable', 'string', 'in:draft,published'],
            'is_featured' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:320'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The post title is required.',
            'title.max' => 'The post title must not exceed 255 characters.',
            'content.required' => 'The post content is required.',
            'slug.unique' => 'This slug is already in use.',
            'category_id.exists' => 'The selected category does not exist.',
            'author_id.exists' => 'The selected author does not exist.',
            'tag_ids.*.exists' => 'One or more selected tags do not exist.',
            'status.in' => 'The status must be either draft or published.',
        ];
    }
}
