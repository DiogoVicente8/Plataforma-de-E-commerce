<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductVariantController extends Controller
{
    public function store(StoreProductVariantRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        $product->variants()->create([
            'size' => $data['size'],
            'color' => $data['color'],
            'stock' => $data['stock'],
            'price_cents' => $this->toCents($data['variant_price'] ?? null),
            'sku' => sprintf('P%03d-%s-%s', $product->id, Str::upper(Str::slug($data['color'])), $data['size']),
        ]);

        return redirect()->route('admin.products.edit', $product)->with('success', __('Variante adicionada.'));
    }

    public function update(Request $request, ProductVariant $variant): RedirectResponse
    {
        $data = $request->validateWithBag('variant', [
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'variant_price' => ['nullable', 'numeric', 'min:0', 'max:99999'],
        ], [
            'stock.required' => __('O stock é obrigatório.'),
            'stock.min' => __('O stock não pode ser negativo.'),
            'variant_price.numeric' => __('O preço tem de ser um número.'),
        ]);

        $variant->update([
            'stock' => $data['stock'],
            'price_cents' => $this->toCents($data['variant_price'] ?? null),
        ]);

        return redirect()->route('admin.products.edit', $variant->product_id)->with('success', __('Variante atualizada.'));
    }

    public function destroy(ProductVariant $variant): RedirectResponse
    {
        $productId = $variant->product_id;
        $variant->delete();

        return redirect()->route('admin.products.edit', $productId)->with('success', __('Variante eliminada.'));
    }

    /** Converte euros (ex.: "19.99") em cêntimos (1999); vazio fica null. */
    private function toCents(mixed $euros): ?int
    {
        return ($euros === null || $euros === '') ? null : (int) round((float) $euros * 100);
    }
}