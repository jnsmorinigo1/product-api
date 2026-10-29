<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_product_can_have_a_price_in_another_currency(): void
    {
        $this->actingAsApiUser();
        $usd = Currency::factory()->create(['exchange_rate' => '1.00000000']);
        $eur = Currency::factory()->create(['exchange_rate' => '2.00000000']);
        $product = Product::factory()->create(['currency_id' => $usd->id, 'price' => '10.00']);

        $this->postJson('/products/'.$product->id.'/prices', [
            'currency_id' => $eur->id,
            'price' => '5.00',
        ])->assertCreated()
            ->assertJsonPath('data.price', '5.00')
            ->assertJsonPath('data.price_in_product_currency', '10.00')
            ->assertJsonPath('data.currency.symbol', $eur->symbol);

        $this->getJson('/products/'.$product->id.'/prices')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.price_in_product_currency', '10.00');

        $this->getJson('/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('data.prices.0.price', '5.00');
    }

    public function test_the_base_currency_cannot_be_stored_as_an_extra_price(): void
    {
        $this->actingAsApiUser();
        $usd = Currency::factory()->create();
        $product = Product::factory()->create(['currency_id' => $usd->id]);

        $this->postJson('/products/'.$product->id.'/prices', [
            'currency_id' => $usd->id,
            'price' => '10.00',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('currency_id');
    }

    public function test_duplicate_currency_prices_are_rejected(): void
    {
        $this->actingAsApiUser();
        $usd = Currency::factory()->create();
        $eur = Currency::factory()->create();
        $product = Product::factory()->create(['currency_id' => $usd->id]);

        $payload = ['currency_id' => $eur->id, 'price' => '10.00'];

        $this->postJson('/products/'.$product->id.'/prices', $payload)->assertCreated();
        $this->postJson('/products/'.$product->id.'/prices', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('currency_id');
    }

    public function test_deleting_a_product_removes_its_prices(): void
    {
        $this->actingAsApiUser();
        $usd = Currency::factory()->create();
        $eur = Currency::factory()->create();
        $product = Product::factory()->create(['currency_id' => $usd->id]);
        $price = $product->prices()->create(['currency_id' => $eur->id, 'price' => '8.00']);

        $this->deleteJson('/products/'.$product->id)->assertNoContent();

        $this->assertDatabaseMissing('product_prices', ['id' => $price->id]);
    }
}
