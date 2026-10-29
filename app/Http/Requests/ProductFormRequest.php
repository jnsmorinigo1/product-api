<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class ProductFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $trimmed = [];

        foreach (['name', 'description'] as $field) {
            if ($this->exists($field) && is_string($this->input($field))) {
                $trimmed[$field] = trim($this->input($field));
            }
        }

        if ($trimmed !== []) {
            $this->merge($trimmed);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function productFieldRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            'tax_cost' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'manufacturing_cost' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
        ];
    }
}
