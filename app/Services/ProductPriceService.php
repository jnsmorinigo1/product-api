<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;

class ProductPriceService
{
    /**
     * Sets the product on each price so the JSON can convert the amount.
     *
     * @return Collection<int, ProductPrice>
     */
    public function listFor(Product $product): Collection
    {
        $product->loadMissing('currency');

        return $product->prices()
            ->with('currency')
            ->orderBy('id')
            ->get()
            ->each(fn (ProductPrice $price) => $price->setRelation('product', $product));
    }

    /**
     * The form request already blocks a duplicate. This catch covers two saves at the same time.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(Product $product, array $attributes): ProductPrice
    {
        try {
            $price = $product->prices()->create($attributes);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'currency_id' => ['This product already has a price in this currency.'],
            ]);
        }

        $product->loadMissing('currency');
        $price->load('currency');
        $price->setRelation('product', $product);

        return $price;
    }
}
