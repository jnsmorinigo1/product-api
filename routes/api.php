<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductPriceController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    Route::get('currencies', [CurrencyController::class, 'index']);
    Route::get('currencies/{currency}', [CurrencyController::class, 'show'])->whereNumber('currency');

    Route::apiResource('products', ProductController::class)->whereNumber('product');
    Route::get('products/{product}/prices', [ProductPriceController::class, 'index'])->whereNumber('product');
    Route::post('products/{product}/prices', [ProductPriceController::class, 'store'])->whereNumber('product');
});
