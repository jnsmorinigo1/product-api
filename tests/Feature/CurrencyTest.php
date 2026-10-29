<?php

namespace Tests\Feature;

use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_currencies_can_be_listed_and_shown(): void
    {
        $this->actingAsApiUser();
        $eur = Currency::factory()->create(['name' => 'Euro', 'symbol' => 'EUR']);
        Currency::factory()->create(['name' => 'US Dollar', 'symbol' => 'USD']);

        $this->getJson('/currencies')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.symbol', 'EUR');

        $this->getJson('/currencies/'.$eur->id)
            ->assertOk()
            ->assertJsonPath('data.name', 'Euro');
    }

    public function test_missing_currencies_return_not_found(): void
    {
        $this->actingAsApiUser();

        $this->getJson('/currencies/999')
            ->assertNotFound()
            ->assertJsonPath('message', 'Currency not found.');
    }
}
