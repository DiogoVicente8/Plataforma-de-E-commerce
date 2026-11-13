<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\ProductVariant;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $items = Cart::items()->where('quantity', '>', 0);

        if ($items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        return view('checkout.create', [
            'items' => $items,
            'total' => $items->sum('subtotal'),
            'addresses' => $request->user()->addresses()->orderByDesc('is_default')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'address_id' => ['required', Rule::exists('addresses', 'id')->where('user_id', $request->user()->id)],
        ], [
            'address_id.required' => __('Escolhe a morada de entrega.'),
        ]);

        $items = Cart::items()->where('quantity', '>', 0);

        if ($items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $address = $request->user()->addresses()->findOrFail($data['address_id']);

        // Tudo ou nada: se faltar stock de uma peça, nada é gravado.
        $order = DB::transaction(function () use ($request, $items, $address) {
            foreach ($items as $item) {
                $variant = $item['variant'];

                // Só desconta se ainda houver stock (evita vender a mesma peça duas vezes).
                $updated = ProductVariant::whereKey($variant->id)
                    ->where('stock', '>=', $item['quantity'])
                    ->decrement('stock', $item['quantity']);

                if (! $updated) {
                    throw ValidationException::withMessages([
                        'cart' => __('Já não há stock suficiente de :product.', ['product' => $variant->product->name]),
                    ]);
                }
            }

            $order = $request->user()->orders()->create([
                'status' => OrderStatus::Pending,
                'total_cents' => $items->sum('subtotal'),
                'shipping_address' => $address->toShippingText(),
            ]);

            $order->items()->createMany($items->map(fn ($item) => [
                'product_id' => $item['variant']->product_id,
                'product_name' => "{$item['variant']->product->name} ({$item['variant']->size} · {$item['variant']->color})",
                'quantity' => $item['quantity'],
                'unit_price_cents' => $item['price'],
            ]));

            return $order;
        });

        $request->session()->forget('cart');

        return redirect()->route('account.orders.show', $order)->with('status', __('Encomenda feita! Obrigado pela tua compra.'));
    }
}
