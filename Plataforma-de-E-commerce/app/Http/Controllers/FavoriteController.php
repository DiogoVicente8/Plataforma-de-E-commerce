<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        return view('shop.favorites', ['products' => $request->user()->favorites()->active()->with(['category', 'images'])->withExists(['variants as in_stock' => fn ($query) => $query->where('stock', '>', 0)])->paginate(12)]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);
        $request->user()->favorites()->syncWithoutDetaching([$product->id]);

        return back()->with('status', 'Produto adicionado aos favoritos.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $request->user()->favorites()->detach($product->id);

        return back()->with('status', 'Produto removido dos favoritos.');
    }
}
