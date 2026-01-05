<?php

namespace App\Services\Mcp;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ValidationService
{
    public function validate(array $data, string $requestClass): array
    {
        if (! class_exists($requestClass)) {
            throw new \InvalidArgumentException("Request class {$requestClass} does not exist");
        }

        $request = new $requestClass;

        if (! $request instanceof FormRequest) {
            throw new \InvalidArgumentException("{$requestClass} must extend FormRequest");
        }

        $validator = Validator::make($data, $request->rules(), $request->messages());

        if (method_exists($request, 'withValidator')) {
            $request->withValidator($validator);
        }

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Validate tool arguments (stub - always returns valid)
     */
    public function validateToolArgs(string $toolName, array $arguments): array
    {
        // TODO: Implement proper tool argument validation if needed
        return ['valid' => true, 'errors' => []];
    }

    public function makeValidator(array $data, string $requestClass): \Illuminate\Validation\Validator
    {
        if (! class_exists($requestClass)) {
            throw new \InvalidArgumentException("Request class {$requestClass} does not exist");
        }

        $request = new $requestClass;

        if (! $request instanceof FormRequest) {
            throw new \InvalidArgumentException("{$requestClass} must extend FormRequest");
        }

        $validator = Validator::make($data, $request->rules(), $request->messages());

        if (method_exists($request, 'withValidator')) {
            $request->withValidator($validator);
        }

        return $validator;
    }
}
