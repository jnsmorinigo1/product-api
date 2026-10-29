<?php

namespace App\Http\Requests;

class StoreProductRequest extends ProductFormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return $this->productFieldRules();
    }
}
