<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Add the demo currencies.
     * exchange_rate is how many US dollars one unit is worth.
     */
    public function run(): void
    {
        $currencies = [
            ['name' => 'US Dollar', 'symbol' => 'USD', 'exchange_rate' => '1.00000000'],
            ['name' => 'Euro', 'symbol' => 'EUR', 'exchange_rate' => '1.08000000'],
            ['name' => 'British Pound', 'symbol' => 'GBP', 'exchange_rate' => '1.27000000'],
            ['name' => 'Brazilian Real', 'symbol' => 'BRL', 'exchange_rate' => '0.18000000'],
            ['name' => 'Paraguayan Guarani', 'symbol' => 'PYG', 'exchange_rate' => '0.00013000'],
        ];

        foreach ($currencies as $currency) {
            Currency::query()->updateOrCreate(
                ['symbol' => $currency['symbol']],
                $currency,
            );
        }
    }
}
