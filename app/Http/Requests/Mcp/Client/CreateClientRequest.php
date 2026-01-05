<?php

namespace App\Http\Requests\Mcp\Client;

use App\Http\Requests\Mcp\BaseToolRequest;

class CreateClientRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:company,individual'],
            'company_name' => ['nullable', 'string', 'max:255', 'required_if:type,company'],
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
            'type.required' => 'The client type is required.',
            'type.in' => 'The client type must be company or individual.',
            'company_name.required_if' => 'The company name is required for company clients.',
            'company_name.max' => 'The company name must not exceed 255 characters.',
            'email.email' => 'Please provide a valid email address.',
            'postal_code.max' => 'The postal code must not exceed 10 characters.',
            'siret.max' => 'The SIRET must not exceed 14 characters.',
        ];
    }
}
