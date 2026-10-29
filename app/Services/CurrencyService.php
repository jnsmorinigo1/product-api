<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Collection;

class CurrencyService
{
    /**
     * @return Collection<int, Currency>
     */
    public function list(): Collection
    {
        return Currency::query()->orderBy('symbol')->get();
    }
}
