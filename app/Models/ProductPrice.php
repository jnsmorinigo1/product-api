<?php

namespace App\Models;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Database\Factories\ProductPriceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'currency_id', 'price'])]
class ProductPrice extends Model
{
    /** @use HasFactory<ProductPriceFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    /**
     * Change this price to another currency. USD is used in the middle.
     */
    public function amountInCurrency(Currency $target): string
    {
        $targetRate = BigDecimal::of((string) $target->exchange_rate);

        if ($targetRate->isLessThanOrEqualTo('0')) {
            return '0.00';
        }

        return BigDecimal::of((string) $this->price)
            ->multipliedBy((string) $this->currency->exchange_rate)
            ->dividedBy($targetRate, 2, RoundingMode::HalfUp)
            ->__toString();
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
