<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Notifications\OrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless(config('cashier.webhook.secret'), 503);
        try {
            $event = Webhook::constructEvent($request->getContent(), $request->header('Stripe-Signature', ''), config('cashier.webhook.secret'));
        } catch (UnexpectedValueException|SignatureVerificationException $exception) {
            return response('Invalid signature', 400);
        }
        if (! in_array($event->type, ['checkout.session.completed', 'checkout.session.expired'], true)) {
            return response('OK');
        }
        $session = $event->data->object;
        DB::transaction(function () use ($event, $session) {
            $order = Order::where('stripe_checkout_session_id', $session->id)->lockForUpdate()->first();
            if (! $order || $order->status !== OrderStatus::Pending) {
                return;
            }
            if ($event->type === 'checkout.session.completed') {
                abort_unless($session->payment_status === 'paid' && $session->currency === 'eur' && $session->amount_total === $order->total_cents, 422);
                $order->update(['status' => OrderStatus::Paid, 'stripe_payment_intent_id' => $session->payment_intent]);
                $order->user?->notify(new OrderStatusChanged($order, OrderStatus::Pending));
            } else {
                foreach ($order->items as $item) {
                    ProductVariant::whereKey($item->product_variant_id)->increment('stock', $item->quantity);
                }
                $order->update(['status' => OrderStatus::Cancelled]);
            }
        });

        return response('OK');
    }
}
