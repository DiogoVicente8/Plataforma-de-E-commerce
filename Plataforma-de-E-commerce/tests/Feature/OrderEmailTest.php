<?php

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\OrderPlaced;
use App\Notifications\OrderStatusChanged;
use App\Services\StripeCheckout;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Stripe\Checkout\Session;

beforeEach(function () {
    config(['shipping.cost_cents' => 0]);
    $this->mock(StripeCheckout::class)->shouldReceive('create')->andReturn(
        Session::constructFrom(['id' => 'cs_test_checkout', 'url' => 'https://checkout.stripe.com/test'])
    );
});

it('emails the customer an order confirmation with the purchased items and total', function () {
    $customer = User::factory()->create();
    $address = Address::factory()->for($customer)->create();
    $variant = ProductVariant::factory()->create(['stock' => 5, 'price_cents' => 1250]);
    Notification::fake();

    $this->actingAs($customer)->withSession(['cart' => [$variant->id => 2]])
        ->post(route('checkout.store'), ['address_id' => $address->id])
        ->assertRedirect()->assertSessionMissing('cart');

    $order = $customer->orders()->sole();
    Notification::assertSentTo($customer, OrderPlaced::class, function ($notification, $channels) use ($customer, $order) {
        $mail = $notification->toMail($customer);
        expect($channels)->toBe(['mail', 'database']);
        expect($notification->orderId)->toBe($order->id);
        expect($notification->toDatabase($customer)['title'])->toBe('Encomenda pendente');
        expect($notification->toDatabase($customer)['body'])->toContain('Falta efetuar o pagamento');
        expect($mail->introLines)->toContain('Total: 25,00 €');
        expect(implode(' ', $mail->introLines))->toContain($order->items()->sole()->product_name);
        expect($mail->actionUrl)->toBe(route('account.orders.show', $order));
        expect((string) $mail->render())->toContain('Ver encomenda', '25,00');

        return true;
    });
    Notification::assertCount(1);
});

it('does not email a confirmation when checkout fails', function () {
    $customer = User::factory()->create();
    $address = Address::factory()->create();
    $variant = ProductVariant::factory()->create();
    Notification::fake();

    $this->actingAs($customer)->withSession(['cart' => [$variant->id => 1]])
        ->post(route('checkout.store'), ['address_id' => $address->id])
        ->assertSessionHasErrors('address_id');

    expect($customer->orders()->count())->toBe(0);
    Notification::assertNothingSent();
});

it('emails only the order owner when the administrator changes its status', function (OrderStatus $status) {
    $customer = User::factory()->create();
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $order = $customer->orders()->create(['status' => OrderStatus::Pending, 'total_cents' => 2500]);
    Notification::fake();

    $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['status' => $status->value])
        ->assertRedirect(route('admin.orders.show', $order));

    expect($order->fresh()->status)->toBe($status);
    Notification::assertSentTo($customer, OrderStatusChanged::class, function ($notification) use ($customer, $order, $status) {
        $mail = $notification->toMail($customer);
        expect($notification->previousStatus)->toBe(OrderStatus::Pending);
        expect($notification->status)->toBe($status);
        expect($mail->introLines)->toContain('Novo estado: '.$status->label());
        expect($mail->actionUrl)->toBe(route('account.orders.show', $order));
        expect((string) $mail->render())->toContain($status->label());

        return true;
    });
    Notification::assertNotSentTo($admin, OrderStatusChanged::class);
    Notification::assertCount(1);
})->with([OrderStatus::Paid, OrderStatus::Shipped, OrderStatus::Cancelled, OrderStatus::Refunded]);

it('does not send an email when the administrator saves the same state', function () {
    $customer = User::factory()->create();
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $order = $customer->orders()->create(['status' => OrderStatus::Paid, 'total_cents' => 2500]);
    Notification::fake();

    $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['status' => 'paid'])
        ->assertRedirect(route('admin.orders.show', $order));

    Notification::assertNothingSent();
});

it('emails the customer when the refund action is used', function () {
    $customer = User::factory()->create();
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $order = $customer->orders()->create(['status' => OrderStatus::Paid, 'total_cents' => 2500]);
    Notification::fake();

    $this->actingAs($admin)->patch(route('admin.orders.refund', $order))
        ->assertRedirect(route('admin.orders.show', $order));

    expect($order->fresh()->status)->toBe(OrderStatus::Refunded);
    Notification::assertSentTo($customer, OrderStatusChanged::class, fn ($notification) => $notification->status === OrderStatus::Refunded);
    Notification::assertCount(1);
});

it('does not notify for an invalid refund or unauthorized status change', function () {
    $customer = User::factory()->create();
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $order = $customer->orders()->create(['status' => OrderStatus::Pending, 'total_cents' => 2500]);
    Notification::fake();

    $this->actingAs($customer)->patch(route('admin.orders.update', $order), ['status' => 'paid'])->assertForbidden();
    $this->actingAs($admin)->patch(route('admin.orders.refund', $order))->assertSessionHasErrors('refund');

    expect($order->fresh()->status)->toBe(OrderStatus::Pending);
    Notification::assertNothingSent();
});

it('updates an order whose customer account has been deleted without sending mail', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $order = Order::create(['status' => OrderStatus::Pending, 'total_cents' => 2500]);
    Notification::fake();

    $this->actingAs($admin)->patch(route('admin.orders.update', $order), ['status' => 'paid'])
        ->assertRedirect(route('admin.orders.show', $order));

    expect($order->fresh()->status)->toBe(OrderStatus::Paid);
    Notification::assertNothingSent();
});

it('queues order mail without contacting the mail server during checkout', function () {
    $customer = User::factory()->create();
    $address = Address::factory()->for($customer)->create();
    $variant = ProductVariant::factory()->create(['stock' => 5]);
    Queue::fake([SendQueuedNotifications::class]);

    $this->actingAs($customer)->withSession(['cart' => [$variant->id => 1]])
        ->post(route('checkout.store'), ['address_id' => $address->id])->assertRedirect();

    Queue::assertPushed(SendQueuedNotifications::class, fn ($job) => $job->notification instanceof OrderPlaced
        && $job->notifiables->contains('id', $customer->id));
});

it('preserves the notified status while later changes wait in the queue', function () {
    $customer = User::factory()->create();
    $order = $customer->orders()->create(['status' => OrderStatus::Paid, 'total_cents' => 2500]);
    $notification = new OrderStatusChanged($order, OrderStatus::Pending);

    $order->update(['status' => OrderStatus::Shipped]);
    $mail = unserialize(serialize($notification))->toMail($customer);

    expect($mail->introLines)->toContain('Novo estado: Paga')->not->toContain('Novo estado: Enviada');
});
