<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

test('admins can create multiple normalized variants with individual stock and prices', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
    $this->get(route('admin.products.create'))->assertOk()->assertSee('Variantes (tamanho e cor)');
    $this->post(route('admin.products.store'), [
        'category_id' => Category::factory()->create()->id,
        'name' => 'Vestido com variantes', 'price' => '39.95', 'is_active' => 1,
        'variants' => [
            ['size' => ' s ', 'color' => ' AZUL ', 'stock' => 0, 'variant_price' => ''],
            ['size' => 'M', 'color' => 'Azul', 'stock' => 5, 'variant_price' => '42.50'],
            ['size' => 'M', 'color' => 'Preto', 'stock' => 2, 'variant_price' => '0'],
        ],
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.products.index'));
    $variants = Product::sole()->variants()->orderBy('id')->get();
    expect($variants)->toHaveCount(3);
    expect($variants[0]->size)->toBe('S');
    expect($variants[0]->color)->toBe('Azul');
    expect($variants[0]->stock)->toBe(0);
    expect($variants[0]->price_cents)->toBeNull();
    expect($variants[1]->stock)->toBe(5);
    expect($variants[1]->price_cents)->toBe(4250);
    expect($variants[2]->price_cents)->toBe(0);
    expect($variants->pluck('sku')->unique())->toHaveCount(3);
});

test('invalid or duplicate variants do not create a partial product', function (array $variants, string $error) {
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->post(route('admin.products.store'), [
            'category_id' => Category::factory()->create()->id, 'name' => 'Produto inválido', 'price' => '20',
            'variants' => $variants,
        ])->assertSessionHasErrors($error);
    expect(Product::count())->toBe(0);
})->with([
    'duplicate after normalization' => [[['size' => 'm', 'color' => ' AZUL ', 'stock' => 1], ['size' => 'M', 'color' => 'azul', 'stock' => 2]], 'variants.1.size'],
    'negative stock' => [[['size' => 'M', 'color' => 'Azul', 'stock' => -1]], 'variants.0.stock'],
    'missing color' => [[['size' => 'M', 'stock' => 1]], 'variants.0.color'],
    'fractional stock' => [[['size' => 'M', 'color' => 'Azul', 'stock' => 1.5]], 'variants.0.stock'],
    'invalid price' => [[['size' => 'M', 'color' => 'Azul', 'stock' => 1, 'variant_price' => -2]], 'variants.0.variant_price'],
]);
