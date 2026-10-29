<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Validation\Validator;

class UpdateProductRequest extends ProductFormRequest
{
    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return $this->productFieldRules();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->has('currency_id')) {
                return;
            }

            $product = $this->route('product');

            if (! $product instanceof Product) {
                return;
            }

            $currencyId = (int) $this->input('currency_id');

            if ($currencyId === (int) $product->currency_id) {
                return;
            }

            $alreadyQuoted = $product->prices()->where('currency_id', $currencyId)->exists();

            if ($alreadyQuoted) {
                $validator->errors()->add(
                    'currency_id',
                    'This currency is already an extra price. Delete that price first if you want it as the base currency.',
                );
            }
        });
    }
}
