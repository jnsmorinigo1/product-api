<?php

namespace App\Models;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'price', 'currency_id', 'tax_cost', 'manufacturing_cost'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'tax_cost' => 'decimal:2',
            'manufacturing_cost' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Product $product): void {
            $product->prices()->delete();
        });
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $term = addcslashes($term, '%_\\');

        return $query->where(function (Builder $query) use ($term): void {
            $query->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Price + tax + manufacturing cost, in the product currency.
     */
    public function totalCost(): string
    {
        return BigDecimal::of((string) ($this->price ?? '0'))
            ->plus((string) ($this->tax_cost ?? '0'))
            ->plus((string) ($this->manufacturing_cost ?? '0'))
            ->toScale(2, RoundingMode::HalfUp)
            ->__toString();
    }

    public function presentPrices(): static
    {
        $this->loadMissing(['currency', 'prices.currency']);

        $this->prices->each(function (ProductPrice $price): void {
            $price->setRelation('product', $this);
        });

        return $this;
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * @return HasMany<ProductPrice, $this>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }
}
