<?php

namespace App\Http\Requests\Mcp\CatalogItem;

use App\Http\Requests\Mcp\BaseToolRequest;

class CreateCatalogItemRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'catalog_category_id' => ['nullable', 'integer', 'exists:catalog_categories,id'],
            'description' => ['nullable', 'string'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string'],
            'vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'default_quantity' => ['nullable', 'numeric', 'min:0.01'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The item name is required.',
            'name.max' => 'The name must not exceed 255 characters.',
            'catalog_category_id.exists' => 'The selected category does not exist.',
            'unit_price.required' => 'The unit price is required.',
            'unit_price.min' => 'The unit price must be at least 0.',
            'unit.required' => 'The unit is required.',
            'vat_rate.required' => 'The VAT rate is required.',
            'vat_rate.max' => 'The VAT rate must not exceed 100%.',
        ];
    }
}
