<?php

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\StripeCheckout;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;

it('audits an order status change by an administrator', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $order = Order::create([
        'user_id' => $admin->id,
        'status' => OrderStatus::Pending,
        'total_cents' => 2500,
    ]);
    $logger = Log::spy();
    $logger->shouldReceive('channel')->with('audit')->andReturnSelf();

    $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['status' => 'paid'])
        ->assertRedirect(route('admin.orders.show', $order));

    expect($order->fresh()->status)->toBe(OrderStatus::Paid);
    Log::shouldHaveReceived('channel')->with('audit')->once();
    Log::shouldHaveReceived('info')->with('Estado da encomenda alterado.', [
        'order_id' => $order->id,
        'previous_status' => 'pending',
        'new_status' => 'paid',
        'user_id' => $admin->id,
    ])->once();
});

it('audits an order created through checkout', function () {
    config(['shipping.cost_cents' => 490, 'shipping.free_from_cents' => 7500]);
    $this->mock(StripeCheckout::class)->shouldReceive('create')->once()->andReturn(
        Session::constructFrom(['id' => 'cs_test_audit', 'url' => 'https://checkout.stripe.com/test'])
    );
    $customer = User::factory()->create();
    $address = Address::factory()->for($customer)->create();
    $variant = ProductVariant::factory()->create(['stock' => 5, 'price_cents' => 1250]);
    $logger = Log::spy();
    $logger->shouldReceive('channel')->with('audit')->andReturnSelf();

    $this->actingAs($customer)->withSession(['cart' => [$variant->id => 2]])
        ->post(route('checkout.store'), ['address_id' => $address->id])
        ->assertRedirect('https://checkout.stripe.com/test')->assertSessionMissing('cart');

    $order = $customer->orders()->sole();
    expect($order->total_cents)->toBe(2990);
    Log::shouldHaveReceived('channel')->with('audit')->once();
    Log::shouldHaveReceived('info')->with('Encomenda criada.', [
        'order_id' => $order->id,
        'user_id' => $customer->id,
        'total_cents' => 2990,
        'item_count' => 2,
    ])->once();
});
