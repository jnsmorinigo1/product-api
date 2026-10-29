<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $product = $this->route('product');
        $productId = $product instanceof Product ? $product->id : 0;
        $baseCurrencyId = $product instanceof Product ? $product->currency_id : null;

        return [
            'currency_id' => [
                'required',
                'integer',
                'exists:currencies,id',
                Rule::notIn(array_filter([$baseCurrencyId])),
                Rule::unique('product_prices', 'currency_id')->where(
                    fn ($query) => $query->where('product_id', $productId),
                ),
            ],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'currency_id.not_in' => 'The base currency is already on the product. Use another currency.',
            'currency_id.unique' => 'This product already has a price in this currency.',
        ];
    }
}
