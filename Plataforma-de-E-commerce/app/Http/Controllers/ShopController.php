<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        return $this->catalog($request);
    }

    public function category(Request $request, Category $category): View
    {
        return $this->catalog($request, $category);
    }

    private function catalog(Request $request, ?Category $category = null): View
    {
        $search = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->where('is_active', true)
            ->when($category, fn ($query) => $query->where('category_id', $category->id))
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->with(['category', 'images'])
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('shop.index', compact('products', 'category', 'search'));
    }
}