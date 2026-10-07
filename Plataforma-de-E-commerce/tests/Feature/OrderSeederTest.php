<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

it('seeds example orders and calculated totals idempotently', function () {
    $this->seed();
    $this->seed();

    expect(Order::count())->toBe(3)
        ->and(OrderItem::count())->toBe(6)
        ->and(Order::with('items')->get()->every(fn (Order $order): bool => $order->total_cents > 0
            && $order->total_cents === $order->items->sum(fn (OrderItem $item): int => $item->subtotalCents())))->toBeTrue()
        ->and(Order::all()->map(fn (Order $order): string => $order->status->value)->all())
        ->toEqualCanonicalizing(['pending', 'paid', 'shipped']);
});

it('seeds an administrator and a customer with the correct roles', function () {
    $this->seed();

    expect(User::where('email', 'admin@loja.test')->firstOrFail()->isAdmin())->toBeTrue()
        ->and(User::where('email', 'cliente@loja.test')->firstOrFail()->isAdmin())->toBeFalse();
});
