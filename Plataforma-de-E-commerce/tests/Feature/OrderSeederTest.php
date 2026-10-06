<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

it('seeds example orders with line items and calculated totals idempotently', function () {
    $this->seed();
    $this->seed();

    expect(Order::count())->toBe(3)
        ->and(OrderItem::count())->toBe(6)
        ->and(User::where('email', 'cliente@loja.test')->exists())->toBeTrue();

    expect(Order::where('status', 'pending')->firstOrFail()->total_cents)->toBe(5297)
        ->and(Order::where('status', 'paid')->firstOrFail()->total_cents)->toBe(3497)
        ->and(Order::where('status', 'shipped')->firstOrFail()->total_cents)->toBe(3797);
});
