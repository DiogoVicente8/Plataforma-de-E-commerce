<?php

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

test('guests can manage a cart with current variant prices', function () {
    $variant = ProductVariant::factory()->create(['stock' => 5, 'price_cents' => 1500]);
    $this->post(route('cart.store', $variant->product), ['variant_id' => $variant->id, 'quantity' => 2, 'price_cents' => 1])
        ->assertRedirect()->assertSessionHas("cart.{$variant->id}", 2);
    $this->get(route('cart.index'))->assertOk()->assertViewHas('total', 3000);
    $this->patch(route('cart.update', $variant), ['quantity' => 3])->assertSessionHas("cart.{$variant->id}", 3);
    $this->delete(route('cart.destroy', $variant))->assertSessionMissing("cart.{$variant->id}");
});

test('cart rejects another products variant and unavailable quantities', function () {
    $variant = ProductVariant::factory()->create(['stock' => 2]);
    $other = Product::factory()->create();
    $this->post(route('cart.store', $other), ['variant_id' => $variant->id, 'quantity' => 1])->assertSessionHasErrors('variant_id');
    $this->post(route('cart.store', $variant->product), ['variant_id' => $variant->id, 'quantity' => 0])->assertSessionHasErrors('quantity');
    $this->post(route('cart.store', $variant->product), ['variant_id' => $variant->id, 'quantity' => 2])->assertSessionHas("cart.{$variant->id}", 2);
    $this->post(route('cart.store', $variant->product), ['variant_id' => $variant->id, 'quantity' => 1])->assertSessionHasErrors('quantity')->assertSessionHas("cart.{$variant->id}", 2);
    $this->patch(route('cart.update', $variant), ['quantity' => 3])->assertSessionHasErrors('quantity');
});

test('cart reconciles stock and hides inactive and deleted products', function () {
    $variant = ProductVariant::factory()->create(['stock' => 1, 'price_cents' => null]);
    $inactive = ProductVariant::factory()->create();
    $inactive->product->update(['is_active' => false]);
    $this->withSession(['cart' => [$variant->id => 4, $inactive->id => 1, 999999 => 1]])
        ->get(route('cart.index'))->assertOk()->assertViewHas('total', $variant->product->price_cents)
        ->assertSessionHas('cart', [$variant->id => 1]);
    $this->post(route('cart.store', $inactive->product), ['variant_id' => $inactive->id, 'quantity' => 1])->assertNotFound();
    $variant->update(['stock' => 0]);
    $this->get(route('cart.index'))->assertOk()->assertSessionHas('cart', []);
});

test('favorites require authentication and are isolated between accounts', function () {
    $product = Product::factory()->create();
    $this->post(route('favorites.store', $product))->assertRedirect(route('login'));
    $user = User::factory()->create();
    $other = User::factory()->create();
    $other->favorites()->attach($product);
    $this->actingAs($user)->post(route('favorites.store', $product))->assertRedirect();
    $this->post(route('favorites.store', $product))->assertRedirect();
    expect($user->favorites()->count())->toBe(1);
    $this->get(route('favorites.index'))->assertOk()->assertSee($product->name);
    $this->delete(route('favorites.destroy', $product))->assertRedirect();
    expect($user->favorites()->count())->toBe(0);
    expect($other->favorites()->count())->toBe(1);
});

test('product pages expose cart and favorite actions and reject inactive favorites', function () {
    $variant = ProductVariant::factory()->create(['stock' => 1]);
    $this->actingAs(User::factory()->create())->get(route('shop.product', $variant->product))
        ->assertOk()->assertSee('Adicionar ao carrinho')->assertSee('Guardar nos favoritos');
    $variant->product->update(['is_active' => false]);
    $this->post(route('favorites.store', $variant->product))->assertNotFound();
});

test('admins cannot access cart or favorites even with direct requests', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $variant = ProductVariant::factory()->create(['stock' => 5]);
    $this->actingAs($admin)->withSession(['cart' => [$variant->id => 1]]);
    $this->get(route('cart.index'))->assertForbidden();
    $this->post(route('cart.store', $variant->product), ['variant_id' => $variant->id, 'quantity' => 1])->assertForbidden();
    $this->patch(route('cart.update', $variant), ['quantity' => 2])->assertForbidden();
    $this->delete(route('cart.destroy', $variant))->assertForbidden();
    $this->get(route('favorites.index'))->assertForbidden();
    $this->post(route('favorites.store', $variant->product))->assertForbidden();
    $this->delete(route('favorites.destroy', $variant->product))->assertForbidden();
    expect($admin->favorites()->count())->toBe(0);
    expect(session('cart'))->toBe([$variant->id => 1]);
    $this->get(route('shop.product', $variant->product))->assertOk()
        ->assertDontSee('Adicionar ao carrinho')->assertDontSee('Guardar nos favoritos')
        ->assertDontSee(route('cart.index'), false)->assertDontSee(route('favorites.index'), false)
        ->assertDontSee('As minhas encomendas')->assertSee('Painel de Administração');
    $this->get(route('home'))->assertOk()->assertDontSee('Guardar nos favoritos');
    $this->get(route('admin.dashboard'))->assertOk();
});

test('admin login goes to the administration panel even after visiting customer pages', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $this->withSession(['url.intended' => route('favorites.index')])->post(route('login'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'))->assertSessionMissing('url.intended');
    $this->assertAuthenticatedAs($admin);
});
