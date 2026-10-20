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
            ->with(['category', 'images'])
            ->latest()
            ->take(8)
            ->get();

        return view('shop.home', compact('categories', 'featured'));
    }
}