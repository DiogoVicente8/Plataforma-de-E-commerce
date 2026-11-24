<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Notifications\OrderPlaced;
use App\Services\StripeCheckout;
use App\Support\Cart;
use App\Support\Shipping;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function pay(Request $request, Order $order, StripeCheckout $stripe): RedirectResponse
    {
        Gate::authorize('view', $order);
        abort_unless($order->user_id === $request->user()->id && $order->status === OrderStatus::Pending && $order->stripe_checkout_session_id, 403);
        $session = $stripe->resume($order);
        if ($session->status !== 'open') {
            return redirect()->route('account.orders.show', $order)->with('status', __('O pagamento já terminou ou expirou. Aguarda a atualização da encomenda.'));
        }

        return redirect()->away($session->url);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $items = Cart::items()->where('quantity', '>', 0);

        if ($items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        return view('checkout.create', [
            'items' => $items,
            'subtotal' => $items->sum('subtotal'),
            'shipping' => Shipping::cost($items->sum('subtotal')),
            'total' => $items->sum('subtotal') + Shipping::cost($items->sum('subtotal')),
            'addresses' => $request->user()->addresses()->orderByDesc('is_default')->latest()->get(),
        ]);
    }

    public function store(Request $request, StripeCheckout $stripe): RedirectResponse
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
        $order = DB::transaction(function () use ($request, $items, $address, $stripe) {
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
                'total_cents' => $items->sum('subtotal') + Shipping::cost($items->sum('subtotal')),
                'shipping_cents' => Shipping::cost($items->sum('subtotal')),
                'shipping_address' => $address->toShippingText(),
            ]);

            $order->items()->createMany($items->map(fn ($item) => [
                'product_id' => $item['variant']->product_id,
                'product_variant_id' => $item['variant']->id,
                'product_name' => "{$item['variant']->product->name} ({$item['variant']->size} · {$item['variant']->color})",
                'quantity' => $item['quantity'],
                'unit_price_cents' => $item['price'],
            ]));

            $session = $stripe->create($order);
            $order->update(['stripe_checkout_session_id' => $session->id]);
            $order->setAttribute('checkout_url', $session->url);

            return $order;
        });

        $request->session()->forget('cart');

        $request->user()->notify(new OrderPlaced($order));

        Log::channel('audit')->info('Encomenda criada.', [
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'total_cents' => $order->total_cents,
            'item_count' => $items->sum('quantity'),
        ]);

        return redirect()->away($order->getAttribute('checkout_url'));
    }
}
