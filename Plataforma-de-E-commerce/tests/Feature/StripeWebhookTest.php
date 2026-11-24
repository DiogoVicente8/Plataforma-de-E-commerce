<?php

use App\Enums\OrderStatus;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\OrderStatusChanged;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    config(['cashier.webhook.secret' => 'whsec_test']);
    $this->variant = ProductVariant::factory()->create(['stock' => 3]);
    $this->order = User::factory()->create()->orders()->create([
        'status' => OrderStatus::Pending, 'total_cents' => 2500, 'stripe_checkout_session_id' => 'cs_test',
    ]);
    $this->order->items()->create([
        'product_id' => $this->variant->product_id, 'product_variant_id' => $this->variant->id,
        'product_name' => 'Produto', 'quantity' => 2, 'unit_price_cents' => 1250,
    ]);
    $this->sendStripeEvent = function (string $type, array $overrides = []) {
        $payload = json_encode(['id' => 'evt_test', 'object' => 'event', 'type' => $type, 'data' => ['object' => array_merge([
            'id' => 'cs_test', 'object' => 'checkout.session', 'payment_status' => 'paid',
            'currency' => 'eur', 'amount_total' => 2500, 'payment_intent' => 'pi_test',
        ], $overrides)]]);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_test');

        return $this->call('POST', '/api/stripe/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
        ], $payload);
    };
});

it('confirms a signed payment once', function () {
    ($this->sendStripeEvent)('checkout.session.completed')->assertOk();
    ($this->sendStripeEvent)('checkout.session.completed')->assertOk();
    Notification::assertSentToTimes($this->order->user, OrderStatusChanged::class, 1);
    expect($this->order->fresh()->status)->toBe(OrderStatus::Paid)
        ->and($this->order->fresh()->stripe_payment_intent_id)->toBe('pi_test')
        ->and($this->variant->fresh()->stock)->toBe(3);
});

it('restores reserved stock only once on expiration', function () {
    ($this->sendStripeEvent)('checkout.session.expired')->assertOk();
    ($this->sendStripeEvent)('checkout.session.expired')->assertOk();
    expect($this->order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($this->variant->fresh()->stock)->toBe(5);
});

it('rejects invalid signatures', function () {
    $this->postJson('/api/stripe/webhook', [])->assertStatus(400);
    expect($this->order->fresh()->status)->toBe(OrderStatus::Pending);
});

it('rejects incorrect payment totals', function () {
    ($this->sendStripeEvent)('checkout.session.completed', ['amount_total' => 1])->assertStatus(422);
    expect($this->order->fresh()->status)->toBe(OrderStatus::Pending);
});
