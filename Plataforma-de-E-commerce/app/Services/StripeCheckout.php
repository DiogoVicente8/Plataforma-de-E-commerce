<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Notifications\OrderStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Laravel\Cashier\Cashier;
use Stripe\Checkout\Session;

class StripeCheckout
{
    public function expire(Order $order): Session
    {
        return Cashier::stripe()->checkout->sessions->expire($order->stripe_checkout_session_id);
    }

    public function cancel(Order $order): void
    {
        if ($order->status !== OrderStatus::Pending) {
            throw ValidationException::withMessages(['order' => __('Só podes anular encomendas pendentes.')]);
        }
        if ($order->stripe_checkout_session_id) {
            $session = $this->resume($order);
            if ($session->status === 'open') {
                $session = $this->expire($order);
            }
            if ($session->status !== 'expired') {
                $this->confirmPayment($order);
                throw ValidationException::withMessages(['order' => __('O pagamento já foi iniciado ou concluído. Não é possível anular esta encomenda.')]);
            }
        }

        DB::transaction(function () use ($order) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->status !== OrderStatus::Pending) {
                return;
            }
            foreach ($lockedOrder->items as $item) {
                ProductVariant::whereKey($item->product_variant_id)->increment('stock', $item->quantity);
            }
            $lockedOrder->update(['status' => OrderStatus::Cancelled]);
        });
        $order->refresh();
    }

    public function confirmPayment(Order $order): void
    {
        if ($order->status !== OrderStatus::Pending || ! $order->stripe_checkout_session_id) {
            return;
        }

        $session = $this->resume($order);
        if ($session->id !== $order->stripe_checkout_session_id
            || $session->payment_status !== 'paid'
            || $session->currency !== 'eur'
            || $session->amount_total !== $order->total_cents) {
            return;
        }

        DB::transaction(function () use ($order, $session) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->status === OrderStatus::Pending) {
                $lockedOrder->update([
                    'status' => OrderStatus::Paid,
                    'stripe_payment_intent_id' => $session->payment_intent,
                ]);
                $lockedOrder->user?->notify(new OrderStatusChanged($lockedOrder, OrderStatus::Pending));
            }
        });
        $order->refresh();
    }

    public function resume(Order $order): Session
    {
        return Cashier::stripe()->checkout->sessions->retrieve($order->stripe_checkout_session_id);
    }

    public function create(Order $order): Session
    {
        $lineItems = $order->items->map(fn ($item) => [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $item->unit_price_cents,
                'product_data' => ['name' => $item->product_name],
            ],
            'quantity' => $item->quantity,
        ])->all();

        if ($order->shipping_cents > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $order->shipping_cents,
                    'product_data' => ['name' => 'Portes de envio'],
                ],
                'quantity' => 1,
            ];
        }

        return Cashier::stripe()->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card', 'mb_way'],
            'customer_email' => $order->user->email,
            'success_url' => route('account.orders.show', $order),
            'cancel_url' => route('account.orders.show', $order),
            'metadata' => ['order_id' => (string) $order->id],
            'line_items' => $lineItems,
        ], ['idempotency_key' => 'order-checkout-'.$order->id]);
    }
}
