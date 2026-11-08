<?php

use App\Enums\UserRole;
use App\Models\OrderItem;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

it('lists only the authenticated customers orders, newest first', function () {
    $customer = User::factory()->create();
    $other = User::factory()->create();
    $older = $customer->orders()->create(['total_cents' => 1500]);
    $newer = $customer->orders()->create(['total_cents' => 2500]);
    $older->forceFill(['created_at' => '2026-01-01 10:00:00'])->save();
    $newer->forceFill(['created_at' => '2026-01-02 10:00:00'])->save();
    $hidden = $other->orders()->create(['total_cents' => 3500]);

    $this->actingAs($customer)->get(route('account.orders.index'))
        ->assertOk()
        ->assertSee(route('account.orders.show', $older))
        ->assertSee(route('account.orders.show', $newer))
        ->assertDontSee(route('account.orders.show', $hidden))
        ->assertViewHas('orders', fn ($orders) => $orders->modelKeys() === [$newer->id, $older->id]);
});

it('shows the customers own order and escapes article names', function () {
    $customer = User::factory()->create();
    $order = $customer->orders()->create(['total_cents' => 3000]);
    $item = new OrderItem;
    $item->product_name = '<script>alert(1)</script>';
    $item->quantity = 2;
    $item->unit_price_cents = 1500;
    $order->items()->save($item);

    $this->actingAs($customer)->get(route('account.orders.show', $order))
        ->assertOk()
        ->assertSee('<script>alert(1)</script>')
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('15,00')
        ->assertSee('30,00');
});

it('forbids opening another customers order', function () {
    $customer = User::factory()->create();
    $order = User::factory()->create()->orders()->create(['total_cents' => 1000]);

    $this->actingAs($customer)->get(route('account.orders.show', $order))->assertForbidden();
});

it('redirects visitors to login', function () {
    $order = User::factory()->create()->orders()->create(['total_cents' => 1000]);

    $this->get(route('account.orders.index'))->assertRedirect(route('login'));
    $this->get(route('account.orders.show', $order))->assertRedirect(route('login'));
});

it('redirects unverified customers to email verification', function () {
    $customer = User::factory()->unverified()->create();
    $order = $customer->orders()->create(['total_cents' => 1000]);

    $this->actingAs($customer)->get(route('account.orders.index'))->assertRedirect(route('verification.notice'));
    $this->get(route('account.orders.show', $order))->assertRedirect(route('verification.notice'));
});

it('forbids customers from accessing admin orders', function () {
    $this->actingAs(User::factory()->create())->get('/admin/orders')->assertForbidden();
});

it('allows administrators to open any customers order', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $order = User::factory()->create()->orders()->create(['total_cents' => 1000]);

    $this->actingAs($admin)->get(route('account.orders.show', $order))->assertOk();
});

it('allows customers to log in and open their orders', function () {
    $customer = User::factory()->create();

    $this->post('/login', ['email' => $customer->email, 'password' => 'password'])
        ->assertSessionHasNoErrors()->assertRedirect();
    $this->assertAuthenticatedAs($customer);
    $this->get(route('account.orders.index'))->assertOk();
});

it('keeps the demo administrator verified and able to access admin', function () {
    $this->seed(DatabaseSeeder::class);
    $admin = User::where('email', 'admin@loja.test')->firstOrFail();

    expect($admin->hasVerifiedEmail())->toBeTrue();
    $this->post('/login', ['email' => $admin->email, 'password' => 'password'])->assertSessionHasNoErrors();
    $this->get('/admin')->assertOk();
});

it('shows an empty state and shop link', function () {
    $this->actingAs(User::factory()->create())->get(route('account.orders.index'))
        ->assertOk()->assertSee('Ainda não fizeste nenhuma encomenda.')->assertSee(route('shop.index'));
});

it('paginates orders in groups of ten', function () {
    $customer = User::factory()->create();
    for ($index = 0; $index < 11; $index++) {
        $customer->orders()->create(['total_cents' => 1000]);
    }

    $this->actingAs($customer)->get(route('account.orders.index'))
        ->assertOk()->assertViewHas('orders', fn ($orders) => $orders->count() === 10 && $orders->total() === 11);
    $this->get(route('account.orders.index', ['page' => 2]))
        ->assertOk()->assertViewHas('orders', fn ($orders) => $orders->count() === 1);
});
