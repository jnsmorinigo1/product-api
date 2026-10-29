<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Add two demo products so you can try the API.
     * If you run this again, those products go back to these values.
     */
    public function run(): void
    {
        $usd = Currency::query()->where('symbol', 'USD')->firstOrFail();
        $eur = Currency::query()->where('symbol', 'EUR')->firstOrFail();
        $pyg = Currency::query()->where('symbol', 'PYG')->firstOrFail();

        $mug = Product::query()->updateOrCreate(
            ['name' => 'Ceramic Mug'],
            [
                'description' => 'White mug, 12 oz.',
                'price' => '12.50',
                'currency_id' => $usd->id,
                'tax_cost' => '1.25',
                'manufacturing_cost' => '3.00',
            ],
        );

        ProductPrice::query()->updateOrCreate(
            ['product_id' => $mug->id, 'currency_id' => $eur->id],
            ['price' => '11.50'],
        );

        ProductPrice::query()->updateOrCreate(
            ['product_id' => $mug->id, 'currency_id' => $pyg->id],
            ['price' => '95000.00'],
        );

        Product::query()->updateOrCreate(
            ['name' => 'Hardcover Notebook'],
            [
                'description' => 'Notebook, A5, 160 pages.',
                'price' => '8.00',
                'currency_id' => $eur->id,
                'tax_cost' => '0.80',
                'manufacturing_cost' => '1.50',
            ],
        );
    }
}
