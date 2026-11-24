<?php

use App\Enums\OrderStatus;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\StripeCheckout;
use Stripe\Checkout\Session;

beforeEach(function () {
    $this->customer = User::factory()->create();
    $this->variant = ProductVariant::factory()->create(['stock' => 3]);
    $this->order = $this->customer->orders()->create([
        'status' => OrderStatus::Pending, 'total_cents' => 1000, 'stripe_checkout_session_id' => 'cs_cancel',
    ]);
    $this->order->items()->create([
        'product_variant_id' => $this->variant->id, 'product_id' => $this->variant->product_id,
        'product_name' => 'Artigo', 'quantity' => 2, 'unit_price_cents' => 500,
    ]);
});

it('expires Stripe checkout and restores stock when cancelling', function () {
    $stripe = $this->partialMock(StripeCheckout::class);
    $stripe->shouldReceive('resume')->once()->andReturn(Session::constructFrom(['status' => 'open']));
    $stripe->shouldReceive('expire')->once()->andReturn(Session::constructFrom(['status' => 'expired']));
    $this->actingAs($this->customer)->post(route('account.orders.cancel', $this->order))
        ->assertRedirect(route('account.orders.show', $this->order));
    expect($this->order->fresh()->status)->toBe(OrderStatus::Cancelled)
        ->and($this->variant->fresh()->stock)->toBe(5);
    $this->post(route('account.orders.cancel', $this->order))->assertSessionHasErrors('order');
    expect($this->variant->fresh()->stock)->toBe(5);
});

it('forbids cancelling another customers order', function () {
    $this->actingAs(User::factory()->create())->post(route('account.orders.cancel', $this->order))->assertForbidden();
    expect($this->variant->fresh()->stock)->toBe(3);
});

it('does not cancel paid orders', function () {
    $this->order->update(['status' => OrderStatus::Paid]);
    $this->actingAs($this->customer)->post(route('account.orders.cancel', $this->order))->assertSessionHasErrors('order');
    expect($this->order->fresh()->status)->toBe(OrderStatus::Paid)
        ->and($this->variant->fresh()->stock)->toBe(3);
});
