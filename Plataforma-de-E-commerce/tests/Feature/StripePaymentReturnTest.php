<?php

use App\Enums\OrderStatus;
use App\Models\User;
use App\Notifications\OrderStatusChanged;
use App\Services\StripeCheckout;
use Illuminate\Support\Facades\Notification;
use Stripe\Checkout\Session;

it('shows a confirmed Stripe payment as paid without a payment button', function () {
    Notification::fake();
    $customer = User::factory()->create();
    $order = $customer->orders()->create([
        'status' => OrderStatus::Pending, 'total_cents' => 5995, 'stripe_checkout_session_id' => 'cs_paid',
    ]);
    $this->partialMock(StripeCheckout::class)->shouldReceive('resume')->once()->andReturn(Session::constructFrom([
        'id' => 'cs_paid', 'payment_status' => 'paid', 'currency' => 'eur', 'amount_total' => 5995, 'payment_intent' => 'pi_paid',
    ]));

    $this->actingAs($customer)->get(route('account.orders.show', $order))
        ->assertOk()->assertSee('Paga')->assertDontSee('Pagar com Stripe');

    expect($order->fresh()->status)->toBe(OrderStatus::Paid)
        ->and($order->fresh()->stripe_payment_intent_id)->toBe('pi_paid');
    Notification::assertSentTo($customer, OrderStatusChanged::class, function ($notification) use ($customer) {
        return $notification->toDatabase($customer)['title'] === 'Encomenda recebida'
            && str_contains($notification->toDatabase($customer)['body'], 'está a ser processada');
    });
});

it('keeps unpaid or mismatched payments pending', function (string $paymentStatus, int $amount) {
    $customer = User::factory()->create();
    $order = $customer->orders()->create([
        'status' => OrderStatus::Pending, 'total_cents' => 5995, 'stripe_checkout_session_id' => 'cs_pending',
    ]);
    $this->partialMock(StripeCheckout::class)->shouldReceive('resume')->once()->andReturn(Session::constructFrom([
        'id' => 'cs_pending', 'payment_status' => $paymentStatus, 'currency' => 'eur', 'amount_total' => $amount,
    ]));

    $this->actingAs($customer)->get(route('account.orders.show', $order))
        ->assertOk()->assertSee('Pendente')->assertSee('Pagar com Stripe');
    expect($order->fresh()->status)->toBe(OrderStatus::Pending);
})->with([['unpaid', 5995], ['paid', 1]]);
