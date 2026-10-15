<?php

namespace App\Http\Controllers\Api;

use App\Actions\DeleteProduct;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreProductRequest;
use App\Http\Requests\Api\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Product::query()
            ->with(['category', 'variants', 'images'])
            ->where('is_active', true)
            ->when($request->query('category'), fn ($query, $category) => $query->where('category_id', $category))
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'));

        match ($request->query('sort', 'newest')) {
            'name' => $query->orderBy('name'),
            'price' => $query->orderBy('price_cents'),
            default => $query->latest(),
        };

        return ProductResource::collection($query->paginate(15));
    }

    public function show(Product $product): ProductResource
    {
        abort_unless($product->is_active, 404);

        return new ProductResource($product->load(['category', 'variants', 'images']));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $product = Product::query()->create($data)->load(['category', 'variants', 'images']);

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('api.v1.products.show', $product));
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product->update($request->validated());

        return new ProductResource($product->load(['category', 'variants', 'images']));
    }

    public function destroy(Product $product, DeleteProduct $deleteProduct): Response
    {
        $deleteProduct->execute($product);

        return response()->noContent();
    }
}
