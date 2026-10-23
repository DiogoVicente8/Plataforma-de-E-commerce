<?php

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

it('renders refunded orders in the admin list and detail pages', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $order = Order::create([
        'user_id' => $admin->id,
        'status' => OrderStatus::Refunded,
        'total_cents' => 2500,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('admin.orders.show', $order))
        ->assertOk();
});

it('does not seed demo administrators in production', function () {
    $this->app['env'] = 'production';

    $this->artisan('db:seed', [
        '--class' => DatabaseSeeder::class,
        '--force' => true,
    ])->assertExitCode(0);

    expect(User::where('email', 'admin@loja.test')->exists())->toBeFalse();
});
