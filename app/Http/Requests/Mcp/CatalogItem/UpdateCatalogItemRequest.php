<?php

namespace App\Http\Requests\Mcp\CatalogItem;

use App\Http\Requests\Mcp\BaseToolRequest;

class UpdateCatalogItemRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:catalog_items,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'catalog_category_id' => ['nullable', 'integer', 'exists:catalog_categories,id'],
            'description' => ['nullable', 'string'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'default_quantity' => ['nullable', 'numeric', 'min:0.01'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The item ID is required.',
            'id.exists' => 'The specified item does not exist.',
            'name.max' => 'The name must not exceed 255 characters.',
            'catalog_category_id.exists' => 'The selected category does not exist.',
            'unit_price.min' => 'The unit price must be at least 0.',
            'vat_rate.max' => 'The VAT rate must not exceed 100%.',
        ];
    }
}
