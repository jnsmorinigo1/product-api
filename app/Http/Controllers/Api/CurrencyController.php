<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CurrencyController extends Controller
{
    public function __construct(private readonly CurrencyService $currencies) {}

    public function index(): AnonymousResourceCollection
    {
        return CurrencyResource::collection($this->currencies->list());
    }

    public function show(Currency $currency): CurrencyResource
    {
        return new CurrencyResource($currency);
    }
}
