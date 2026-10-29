<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    /**
     * @param  array{search?: string|null, currency_id?: int|null, per_page?: int|null}  $filters
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        $paginator = Product::query()
            ->with(['currency', 'prices.currency'])
            ->search($filters['search'] ?? null)
            ->when(
                filled($filters['currency_id'] ?? null),
                fn ($query) => $query->where('currency_id', $filters['currency_id']),
            )
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        $paginator->getCollection()->each(fn (Product $product) => $product->presentPrices());

        return $paginator;
    }

    /**
     * Loads the currency and the extra prices for the JSON.
     */
    public function show(Product $product): Product
    {
        return $product->presentPrices();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Product
    {
        return Product::query()->create($attributes)->presentPrices();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Product $product, array $attributes): Product
    {
        $product->update($attributes);

        return $product->presentPrices();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
