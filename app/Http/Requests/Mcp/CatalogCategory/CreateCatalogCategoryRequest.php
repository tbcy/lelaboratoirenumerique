<?php

namespace App\Http\Requests\Mcp\CatalogCategory;

use App\Http\Requests\Mcp\BaseToolRequest;

class CreateCatalogCategoryRequest extends BaseToolRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:7'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'name.max' => 'The name must not exceed 255 characters.',
        ];
    }
}
