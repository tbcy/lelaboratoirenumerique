<?php

namespace App\Http\Requests\Mcp\SocialConnection;

use App\Http\Requests\Mcp\BaseToolRequest;

class CreateSocialConnectionRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'platform' => ['required', 'string', 'in:twitter,linkedin,instagram,facebook'],
            'is_active' => ['nullable', 'boolean'],
            'credentials' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The connection name is required.',
            'name.max' => 'The name must not exceed 255 characters.',
            'platform.required' => 'The platform is required.',
            'platform.in' => 'The platform must be twitter, linkedin, instagram, or facebook.',
        ];
    }
}
