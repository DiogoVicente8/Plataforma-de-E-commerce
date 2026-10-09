<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with('category')
            ->withCount('variants')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.products.index', ['products' => $products]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        Product::create($request->productData());

        return redirect()->route('admin.products.index')->with('success', __('Produto criado.'));
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->productData());

        return redirect()->route('admin.products.index')->with('success', __('Produto atualizado.'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        Storage::disk('public')->delete($product->images->pluck('path')->all());
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', __('Produto eliminado.'));
    }
}