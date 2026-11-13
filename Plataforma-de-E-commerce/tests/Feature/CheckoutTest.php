<?php

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\ProductVariant;
use App\Models\User;

beforeEach(function () {
    $this->customer = User::factory()->create();
    $this->address = Address::factory()->for($this->customer)->create(['recipient_name' => 'Ana Silva']);
    $this->variant = ProductVariant::factory()->create(['stock' => 5, 'price_cents' => null]);
});

it('places an order, takes the stock and empties the cart', function () {
    $this->actingAs($this->customer)
        ->withSession(['cart' => [$this->variant->id => 2]])
        ->post(route('checkout.store'), ['address_id' => $this->address->id])
        ->assertRedirect()
        ->assertSessionMissing('cart');

    $order = $this->customer->orders()->sole();

    expect($order->status)->toBe(OrderStatus::Pending)
        ->and($order->total_cents)->toBe($this->variant->product->price_cents * 2)
        ->and($order->shipping_address)->toContain('Ana Silva')
        ->and($order->items()->sole()->quantity)->toBe(2)
        ->and($this->variant->fresh()->stock)->toBe(3);
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
