<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductPriceRequest;
use App\Http\Resources\ProductPriceResource;
use App\Models\Product;
use App\Services\ProductPriceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductPriceController extends Controller
{
    public function __construct(private readonly ProductPriceService $prices) {}

    public function index(Product $product): AnonymousResourceCollection
    {
        return ProductPriceResource::collection(
            $this->prices->listFor($product),
        );
    }

    public function store(StoreProductPriceRequest $request, Product $product): JsonResponse
    {
        $price = $this->prices->create($product, $request->validated());

        return ProductPriceResource::make($price)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
