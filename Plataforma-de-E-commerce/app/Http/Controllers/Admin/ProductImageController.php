<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductImageRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function store(StoreProductImageRequest $request, Product $product): RedirectResponse
    {
        $path = $request->file('image')->store('products', 'public');

        $product->images()->create([
            'path' => $path,
            'alt_text' => $request->validated('alt_text') ?: $product->name,
            'position' => ($product->images()->max('position') ?? -1) + 1,
        ]);

        return redirect()->route('admin.products.edit', $product)->with('success', __('Imagem adicionada.'));
    }

    public function destroy(ProductImage $image): RedirectResponse
    {
        $productId = $image->product_id;

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return redirect()->route('admin.products.edit', $productId)->with('success', __('Imagem eliminada.'));
    }
}