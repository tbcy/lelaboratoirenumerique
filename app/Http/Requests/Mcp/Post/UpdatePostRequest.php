<?php

namespace App\Http\Requests\Mcp\Post;

use App\Http\Requests\Mcp\BaseToolRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends BaseToolRequest
{
    public function rules(): array
    {
        $postId = request()->input('id');

        return [
            'id' => ['required', 'integer', 'exists:posts,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('posts', 'slug')->ignore($postId),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'status' => ['nullable', 'string', 'in:draft,published'],
            'is_featured' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The post ID is required.',
            'id.exists' => 'The specified post does not exist.',
            'title.max' => 'The post title must not exceed 255 characters.',
            'slug.unique' => 'This slug is already in use by another post.',
            'category_id.exists' => 'The selected category does not exist.',
            'tag_ids.*.exists' => 'One or more selected tags do not exist.',
            'status.in' => 'The status must be either draft or published.',
        ];
    }
}
