<?php

namespace App\Http\Requests\Mcp\Client;

use App\Http\Requests\Mcp\BaseToolRequest;

class UpdateClientRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:clients,id'],
            'type' => ['nullable', 'string', 'in:company,individual'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:prospect,active,inactive'],
            'tags' => ['nullable', 'array'],
            'address' => ['nullable', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'siret' => ['nullable', 'string', 'max:14'],
            'vat_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The client ID is required.',
            'id.exists' => 'The specified client does not exist.',
            'type.in' => 'The client type must be company or individual.',
            'company_name.max' => 'The company name must not exceed 255 characters.',
            'email.email' => 'Please provide a valid email address.',
            'postal_code.max' => 'The postal code must not exceed 10 characters.',
            'siret.max' => 'The SIRET must not exceed 14 characters.',
        ];
    }
}
