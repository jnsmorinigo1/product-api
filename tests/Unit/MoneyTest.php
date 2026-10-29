<?php

namespace Tests\Unit;

use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductPrice;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    public function test_it_sums_price_tax_and_manufacturing_cost(): void
    {
        $product = new Product([
            'price' => '12.50',
            'tax_cost' => '1.25',
            'manufacturing_cost' => '3.00',
        ]);

        $this->assertSame('16.75', $product->totalCost());
    }

    public function test_it_converts_a_price_into_the_product_currency(): void
    {
        $dollar = new Currency(['exchange_rate' => '1.00000000']);
        $euro = new Currency(['exchange_rate' => '2.00000000']);

        $product = new Product([
            'price' => '10.00',
            'tax_cost' => '0.00',
            'manufacturing_cost' => '0.00',
        ]);
        $product->setRelation('currency', $dollar);

        $price = new ProductPrice(['price' => '5.00']);
        $price->setRelation('currency', $euro);
        $price->setRelation('product', $product);

        $this->assertSame('10.00', $price->amountInCurrency($dollar));
    }
}
