<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Currency>
 */
class CurrencyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'symbol' => strtoupper(fake()->unique()->lexify('???')),
            'exchange_rate' => number_format(fake()->randomFloat(4, 0.01, 5), 8, '.', ''),
        ];
    }
}
