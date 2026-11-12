<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categories = Category::query()
            ->whereHas('products', fn ($query) => $query->where('is_active', true))
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get();

        $featured = Product::query()
            ->where('is_active', true)
            ->with(['category', 'images'])->withExists(['variants as in_stock' => fn ($query) => $query->where('stock', '>', 0)])
            ->latest()
            ->take(8)
            ->get();

        return view('shop.home', compact('categories', 'featured'));
    }
}
