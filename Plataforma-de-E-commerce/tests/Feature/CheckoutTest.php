<?php

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\StripeCheckout;
use App\Support\Shipping;
use Illuminate\Support\Facades\Notification;
use Stripe\Checkout\Session;

beforeEach(function () {
    config(['shipping.cost_cents' => 490, 'shipping.free_from_cents' => 7500]);
    Notification::fake();
    $this->mock(StripeCheckout::class)->shouldReceive('create')->andReturn(
        Session::constructFrom(['id' => 'cs_test_checkout', 'url' => 'https://checkout.stripe.com/test'])
    );
    $this->customer = User::factory()->create();
    $this->address = Address::factory()->for($this->customer)->create(['recipient_name' => 'Ana Silva']);
    $this->variant = ProductVariant::factory()->create(['stock' => 5, 'price_cents' => null]);
});

it('places an order, takes the stock and empties the cart', function () {
    $this->actingAs($this->customer)
        ->withSession(['cart' => [$this->variant->id => 2]])
        ->post(route('checkout.store'), ['address_id' => $this->address->id])
        ->assertRedirect('https://checkout.stripe.com/test')
        ->assertSessionMissing('cart');

    $order = $this->customer->orders()->sole();

    expect($order->status)->toBe(OrderStatus::Pending)
        ->and($order->total_cents)->toBe($this->variant->product->price_cents * 2 + Shipping::cost($this->variant->product->price_cents * 2))
        ->and($order->shipping_address)->toContain('Ana Silva')
        ->and($order->items()->sole()->quantity)->toBe(2)
        ->and($this->variant->fresh()->stock)->toBe(3);
});

it('always redirects to Stripe even if an old pay later option is submitted', function () {
    $response = $this->actingAs($this->customer)
        ->withSession(['cart' => [$this->variant->id => 1]])
        ->post(route('checkout.store'), ['address_id' => $this->address->id, 'payment_timing' => 'later']);

    $order = $this->customer->orders()->sole();
    $response->assertRedirect('https://checkout.stripe.com/test')->assertSessionMissing('cart');
    expect($order->status)->toBe(OrderStatus::Pending)
        ->and($order->stripe_checkout_session_id)->toBe('cs_test_checkout');
});

it('redirects directly to Stripe when paying now', function () {
    $this->actingAs($this->customer)
        ->withSession(['cart' => [$this->variant->id => 1]])
        ->post(route('checkout.store'), ['address_id' => $this->address->id, 'payment_timing' => 'now'])
        ->assertRedirect('https://checkout.stripe.com/test');
});

it('does not accept another customers address', function () {
    $otherAddress = Address::factory()->create();

    $this->actingAs($this->customer)
        ->withSession(['cart' => [$this->variant->id => 1]])
        ->post(route('checkout.store'), ['address_id' => $otherAddress->id])
        ->assertSessionHasErrors('address_id');

    expect($this->customer->orders()->count())->toBe(0);
});

it('sends an empty cart back to the cart page', function () {
    $this->actingAs($this->customer)
        ->get(route('checkout.create'))
        ->assertRedirect(route('cart.index'));
});

it('includes shipping in the checkout and saved total without trusting submitted prices', function (int $subtotal, int $shipping) {
    $this->variant->update(['price_cents' => $subtotal]);
    $this->actingAs($this->customer)->withSession(['cart' => [$this->variant->id => 1]])
        ->get(route('checkout.create'))
        ->assertOk()->assertViewHas('shipping', $shipping)->assertViewHas('total', $subtotal + $shipping);
    $this->post(route('checkout.store'), [
        'address_id' => $this->address->id, 'shipping_cents' => 0, 'total_cents' => 1,
    ])->assertRedirect('https://checkout.stripe.com/test');
    $order = $this->customer->orders()->sole();
    expect($order->shipping_cents)->toBe($shipping)->and($order->total_cents)->toBe($subtotal + $shipping);
    config(['shipping.cost_cents' => 900]);
    expect($order->fresh()->shipping_cents)->toBe($shipping);
})->with([[7499, 490], [7500, 0], [9000, 0]]);
