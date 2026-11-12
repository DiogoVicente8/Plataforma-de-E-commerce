<?php

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\BikiniCatalogSeeder;
use Illuminate\Support\Facades\Storage;

test('bikini collection is visible to customers with local photographs and sized stock', function () {
    Storage::fake('public');
    $this->seed(BikiniCatalogSeeder::class);
    $category = Category::where('slug', 'bikinis')->sole();
    expect($category->products()->count())->toBe(6);
    foreach ($category->products()->with(['images', 'variants'])->get() as $product) {
        expect($product->is_active)->toBeTrue();
        expect($product->variants->pluck('size')->sort()->values()->all())->toBe(['L', 'M', 'S', 'XS']);
        Storage::disk('public')->assertExists($product->images->sole()->path);
    }
    $this->get(route('home'))->assertOk()->assertSee('Bikinis')->assertSee(route('shop.category', $category), false);
    $this->get(route('shop.category', $category))->assertOk()->assertViewHas('products', fn ($products) => $products->total() === 6)->assertSee('Esgotado');
    $product = Product::where('category_id', $category->id)->first();
    $this->get(route('shop.product', $product))->assertOk()->assertSee($product->images->sole()->path);
    $variant = $product->variants()->first();
    $variant->update(['stock' => 37]);
    $this->seed(BikiniCatalogSeeder::class);
    expect($category->products()->count())->toBe(6);
    expect($variant->fresh()->stock)->toBe(37);
    expect($product->images()->count())->toBe(1);
});
