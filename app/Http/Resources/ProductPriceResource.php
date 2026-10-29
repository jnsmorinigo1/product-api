<?php

namespace App\Http\Resources;

use App\Models\ProductPrice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProductPrice */
class ProductPriceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'currency_id' => $this->currency_id,
            'price' => $this->price,
            'price_in_product_currency' => $this->when(
                $this->relationLoaded('currency')
                    && $this->relationLoaded('product')
                    && $this->product->relationLoaded('currency'),
                fn () => $this->amountInCurrency($this->product->currency),
            ),
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
