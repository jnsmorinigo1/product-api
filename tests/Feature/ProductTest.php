<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_can_be_created_and_read(): void
    {
        $this->actingAsApiUser();
        $currency = Currency::factory()->create(['symbol' => 'USD', 'exchange_rate' => '1.00000000']);

        $created = $this->postJson('/products', $this->payload($currency->id))
            ->assertCreated()
            ->assertJsonPath('data.name', 'Desk Lamp')
            ->assertJsonPath('data.price', '42.00')
            ->assertJsonPath('data.total_cost', '55.20')
            ->assertJsonPath('data.currency.symbol', 'USD')
            ->assertJsonPath('data.prices', []);

        $id = $created->json('data.id');

        $this->getJson('/products/'.$id)
            ->assertOk()
            ->assertJsonPath('data.description', 'A small desk lamp.');

        $this->getJson('/products')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_create_requires_a_known_currency_and_money_fields(): void
    {
        $this->actingAsApiUser();

        $this->post('/products', [])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors']);

        $this->postJson('/products', $this->payload(999))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['currency_id']);
    }

    public function test_products_can_be_updated_and_deleted(): void
    {
        $this->actingAsApiUser();
        $currency = Currency::factory()->create();
        $product = Product::factory()->create(['currency_id' => $currency->id]);

        $this->putJson('/products/'.$product->id, $this->payload($currency->id, [
            'name' => 'Updated Lamp',
        ]))->assertOk()
            ->assertJsonPath('data.name', 'Updated Lamp');

        $this->deleteJson('/products/'.$product->id)->assertNoContent();

        $this->getJson('/products/'.$product->id)
            ->assertNotFound()
            ->assertJsonPath('message', 'Product not found.');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_base_currency_cannot_switch_to_an_existing_extra_price(): void
    {
        $this->actingAsApiUser();
        $usd = Currency::factory()->create();
        $eur = Currency::factory()->create();
        $product = Product::factory()->create(['currency_id' => $usd->id]);
        $product->prices()->create(['currency_id' => $eur->id, 'price' => '10.00']);

        $this->putJson('/products/'.$product->id, $this->payload($eur->id))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('currency_id');
    }

    public function test_products_can_be_searched_and_filtered(): void
    {
        $this->actingAsApiUser();
        $usd = Currency::factory()->create();
        $eur = Currency::factory()->create();

        Product::factory()->create([
            'name' => 'Oak Chair',
            'description' => 'A wooden chair.',
            'currency_id' => $usd->id,
        ]);
        Product::factory()->create([
            'name' => 'Steel Shelf',
            'description' => 'Industrial shelf.',
            'currency_id' => $eur->id,
        ]);

        $this->getJson('/products?search=chair')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Oak Chair');

        $this->getJson('/products?currency_id='.$eur->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Steel Shelf');
    }

    public function test_product_lists_are_paginated(): void
    {
        $this->actingAsApiUser();
        Product::factory()->count(3)->create();

        $this->getJson('/products?per_page=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.per_page', 1);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(int $currencyId, array $overrides = []): array
    {
        return array_merge([
            'name' => 'Desk Lamp',
            'description' => 'A small desk lamp.',
            'price' => '42.00',
            'currency_id' => $currencyId,
            'tax_cost' => '4.20',
            'manufacturing_cost' => '9.00',
        ], $overrides);
    }
}
