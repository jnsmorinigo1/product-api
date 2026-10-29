<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function index(IndexProductRequest $request): AnonymousResourceCollection
    {
        return ProductResource::collection(
            $this->products->paginate($request->validated()),
        );
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->products->create($request->validated());

        return ProductResource::make($product)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($this->products->show($product));
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        return new ProductResource(
            $this->products->update($product, $request->validated()),
        );
    }

    public function destroy(Product $product): Response
    {
        $this->products->delete($product);

        return response()->noContent();
    }
}
