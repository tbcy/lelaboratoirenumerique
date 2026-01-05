<?php

namespace App\Http\Requests\Mcp\CatalogCategory;

use App\Http\Requests\Mcp\BaseToolRequest;

class UpdateCatalogCategoryRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:catalog_categories,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:7'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The category ID is required.',
            'id.exists' => 'The specified category does not exist.',
            'name.max' => 'The name must not exceed 255 characters.',
        ];
    }
}
