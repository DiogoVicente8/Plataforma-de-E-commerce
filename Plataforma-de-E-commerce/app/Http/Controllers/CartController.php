<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $request->session()->get('cart', []);
        $variants = ProductVariant::query()->with('product')->whereIn('id', array_keys($cart))
            ->whereHas('product', fn ($query) => $query->active())->get();
        $items = $variants->map(function ($variant) use ($cart) {
            $quantity = min((int) $cart[$variant->id], $variant->stock);
            $price = $variant->price_cents ?? $variant->product->price_cents;

            return ['variant' => $variant, 'quantity' => $quantity, 'price' => $price, 'subtotal' => $price * $quantity];
        });
        // Reconcile the basket with current availability; prices always come from the database.
        $request->session()->put('cart', $items->filter(fn ($item) => $item['quantity'] > 0)
            ->mapWithKeys(fn ($item) => [$item['variant']->id => $item['quantity']])->all());

        return view('shop.cart', ['items' => $items, 'total' => $items->sum('subtotal')]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);
        $data = $request->validate(['variant_id' => ['required', 'integer'], 'quantity' => ['required', 'integer', 'min:1']]);
        $variant = $product->variants()->find($data['variant_id']);
        if (! $variant) {
            throw ValidationException::withMessages(['variant_id' => 'Selecione um tamanho e uma cor válidos.']);
        }
        $cart = $request->session()->get('cart', []);
        $quantity = ($cart[$variant->id] ?? 0) + $data['quantity'];
        $this->validateStock($variant, $quantity);
        $cart[$variant->id] = $quantity;
        $request->session()->put('cart', $cart);

        return back()->with('status', 'Produto adicionado ao carrinho.');
    }

    public function update(Request $request, ProductVariant $variant): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);
        $cart = $request->session()->get('cart', []);
        abort_unless(isset($cart[$variant->id]) && $variant->product->is_active, 404);
        $this->validateStock($variant, $data['quantity']);
        $cart[$variant->id] = $data['quantity'];
        $request->session()->put('cart', $cart);

        return back()->with('status', 'Carrinho atualizado.');
    }

    public function destroy(Request $request, ProductVariant $variant): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$variant->id]);
        $request->session()->put('cart', $cart);

        return back()->with('status', 'Produto removido do carrinho.');
    }

    private function validateStock(ProductVariant $variant, int $quantity): void
    {
        if ($quantity > $variant->stock) {
            throw ValidationException::withMessages(['quantity' => 'A quantidade pedida excede o stock disponível.']);
        }
    }
}
