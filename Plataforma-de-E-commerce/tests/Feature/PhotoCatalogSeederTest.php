<?php

use App\Models\Product;
use Database\Seeders\PhotoCatalogSeeder;
use Illuminate\Support\Facades\Storage;

test('photo catalog provides local images and mixed availability without duplicates or overwriting stock', function () {
    Storage::fake('public');
    $this->seed(PhotoCatalogSeeder::class);
    expect(Product::count())->toBe(12);
    expect(Product::whereDoesntHave('variants', fn ($query) => $query->where('stock', '>', 0))->count())->toBe(4);
    expect(Product::whereHas('variants', fn ($query) => $query->where('stock', '>', 0))->count())->toBe(8);
    foreach (Product::with('images')->get() as $product) {
        expect($product->images)->toHaveCount(1);
        Storage::disk('public')->assertExists($product->images->first()->path);
    }
    $product = Product::first();
    $variant = $product->variants()->first();
    $variant->update(['stock' => 37]);
    $product->update(['price_cents' => 1234, 'is_active' => false]);
    $this->seed(PhotoCatalogSeeder::class);
    expect(Product::count())->toBe(12);
    expect($variant->fresh()->stock)->toBe(37);
    expect($product->fresh()->price_cents)->toBe(1234);
    expect($product->fresh()->is_active)->toBeFalse();
    expect($product->images()->count())->toBe(1);
});

test('photo catalog shows sold out labels and filters unavailable items', function () {
    Storage::fake('public');
    $this->seed(PhotoCatalogSeeder::class);
    $this->get(route('shop.index'))->assertOk()->assertSee('Esgotado');
    $this->get(route('shop.index', ['in_stock' => 1]))->assertOk()->assertViewHas('products', function ($products) {
        return $products->total() === 8 && $products->every(fn ($product) => $product->in_stock);
    });
});
