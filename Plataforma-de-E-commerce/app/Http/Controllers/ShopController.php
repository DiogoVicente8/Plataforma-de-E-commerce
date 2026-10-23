<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogFilterRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(CatalogFilterRequest $request): View
    {
        return $this->catalog($request);
    }

    public function category(CatalogFilterRequest $request, Category $category): View
    {
        return $this->catalog($request, $category);
    }

    private function catalog(CatalogFilterRequest $request, ?Category $category = null): View
    {
        $filters = $request->validated();

        $products = Product::query()
            ->active()
            ->when($category, fn ($q) => $q->where('category_id', $category->id))
            ->search($filters['q'] ?? null)
            ->withVariant($filters['size'] ?? null, $filters['color'] ?? null, $request->boolean('in_stock'))
            ->priceBetween($filters['min_price'] ?? null, $filters['max_price'] ?? null)
            ->sorted($filters['sort'] ?? null)
            ->with(['category', 'images'])
            ->paginate(12)
            ->withQueryString();

        return view('shop.index', [
            'products' => $products,
            'category' => $category,
            'filters' => $filters,
            'search' => $filters['q'] ?? '',
            'sizes' => ProductVariant::distinct()->orderBy('size')->pluck('size'),
            'colors' => ProductVariant::distinct()->orderBy('color')->pluck('color'),
        ]);
    }
}