<?php

namespace App\Http\Controllers\Admin;

use App\Actions\DeleteProduct;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

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
            'product' => new Product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $path = null;

        try {
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                if ($path === false) {
                    throw new \RuntimeException('Não foi possível guardar a imagem.');
                }
            }

            DB::transaction(function () use ($request, $path): void {
                $product = Product::create($request->productData());
                foreach ($request->validated('variants') ?? [] as $variant) {
                    $product->variants()->create([
                        'size' => $variant['size'],
                        'color' => $variant['color'],
                        'stock' => $variant['stock'],
                        'price_cents' => isset($variant['variant_price']) && $variant['variant_price'] !== ''
                            ? (int) round((float) $variant['variant_price'] * 100) : null,
                        'sku' => 'P'.$product->id.'-'.Str::uuid(),
                    ]);
                }
                if ($path) {
                    $product->images()->create([
                        'path' => $path,
                        'alt_text' => $product->name,
                        'position' => 0,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }

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
        $path = null;
        $deletedPaths = [];

        try {
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                if ($path === false) {
                    throw new \RuntimeException('Não foi possível guardar a imagem.');
                }
            }

            DB::transaction(function () use ($request, $product, $path, &$deletedPaths): void {
                $product->update($request->productData());
                $product->variants()->whereIn('id', $request->validated('removed_variants') ?? [])->delete();
                foreach ($request->validated('variants') ?? [] as $variant) {
                    $data = [
                        'stock' => $variant['stock'],
                        'price_cents' => isset($variant['variant_price']) && $variant['variant_price'] !== ''
                            ? (int) round((float) $variant['variant_price'] * 100) : null,
                    ];
                    if (! empty($variant['id'])) {
                        $product->variants()->whereKey($variant['id'])->update($data);
                    } else {
                        $product->variants()->create($data + [
                            'size' => $variant['size'],
                            'color' => $variant['color'],
                            'sku' => 'P'.$product->id.'-'.Str::uuid(),
                        ]);
                    }
                }
                $images = $product->images()->whereIn('id', $request->validated('removed_images') ?? [])->get();
                $deletedPaths = $images->pluck('path')->all();
                $product->images()->whereIn('id', $images->modelKeys())->delete();
                if ($path) {
                    $product->images()->create([
                        'path' => $path,
                        'alt_text' => $request->validated('alt_text') ?: $product->name,
                        'position' => ($product->images()->max('position') ?? -1) + 1,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }

        Storage::disk('public')->delete($deletedPaths);

        return redirect()->route('admin.products.index')->with('success', __('Produto atualizado.'));
    }

    public function destroy(Product $product, DeleteProduct $deleteProduct): RedirectResponse
    {
        $deleteProduct->execute($product);

        return redirect()->route('admin.products.index')->with('success', __('Produto eliminado.'));
    }
}
