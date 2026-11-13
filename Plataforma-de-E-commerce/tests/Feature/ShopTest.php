<?php

use App\Models\Product;
use App\Models\ProductVariant;

it('only shows active products in the catalog', function () {
    $active = Product::factory()->create(['name' => 'Vestido visível']);
    $inactive = Product::factory()->create(['name' => 'Vestido escondido', 'is_active' => false]);

    $this->get(route('shop.index'))
        ->assertOk()
        ->assertSee($active->name)
        ->assertDontSee($inactive->name);
});

it('filters the catalog by size and price', function () {
    $cheapM = Product::factory()->create(['name' => 'Blusa barata', 'price_cents' => 1990]);
    $expensiveM = Product::factory()->create(['name' => 'Casaco caro', 'price_cents' => 8990]);
    $cheapL = Product::factory()->create(['name' => 'Saia em L', 'price_cents' => 1990]);
    ProductVariant::factory()->for($cheapM)->create(['size' => 'M']);
    ProductVariant::factory()->for($expensiveM)->create(['size' => 'M']);
    ProductVariant::factory()->for($cheapL)->create(['size' => 'L']);

    $this->get(route('shop.index', ['size' => 'M', 'max_price' => 30]))
        ->assertSee($cheapM->name)
        ->assertDontSee($expensiveM->name)
        ->assertDontSee($cheapL->name);
});

it('lists the sizes from XXS to XXL', function () {
    foreach (['XL', 'S', 'XXS', 'M'] as $size) {
        ProductVariant::factory()->create(['size' => $size]);
    }

    $this->get(route('shop.index'))
        ->assertViewHas('sizes', fn ($sizes) => $sizes->all() === ['XXS', 'S', 'M', 'XL']);
});

it('shows the product page and hides inactive products', function () {
    $product = Product::factory()->create();
    $inactive = Product::factory()->create(['is_active' => false]);

    $this->get(route('shop.product', $product))->assertOk()->assertSee($product->name);
    $this->get(route('shop.product', $inactive))->assertNotFound();
});
