<?php

namespace App\Http\Requests\Mcp\SocialConnection;

use App\Http\Requests\Mcp\BaseToolRequest;

class UpdateSocialConnectionRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:social_connections,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'in:twitter,linkedin,instagram,facebook'],
            'is_active' => ['nullable', 'boolean'],
            'credentials' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The connection ID is required.',
            'id.exists' => 'The specified connection does not exist.',
            'name.max' => 'The name must not exceed 255 characters.',
            'platform.in' => 'The platform must be twitter, linkedin, instagram, or facebook.',
        ];
    }
}
