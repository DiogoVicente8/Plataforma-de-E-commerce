<?php

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function pageProductData(Product $product): array
{
    return ['category_id' => $product->category_id, 'name' => 'Produto atualizado', 'slug' => $product->slug, 'price' => '45.50', 'is_active' => 1];
}

function pageVariantData(ProductVariant $variant, int $stock): array
{
    return ['id' => $variant->id, 'size' => $variant->size, 'color' => $variant->color, 'stock' => $stock, 'variant_price' => '12.50'];
}

test('one save updates product and variants and applies pending additions and removals', function () {
    Storage::fake('public');
    $product = Product::factory()->create();
    $first = ProductVariant::factory()->create(['product_id' => $product->id, 'size' => 'S', 'color' => 'Azul / Branco', 'stock' => 3]);
    $second = ProductVariant::factory()->create(['product_id' => $product->id, 'size' => 'M', 'color' => 'Azul', 'stock' => 4]);
    $removed = ProductVariant::factory()->create(['product_id' => $product->id, 'size' => 'L', 'color' => 'Azul']);
    Storage::disk('public')->put('products/old.png', 'old image');
    $image = $product->images()->create(['path' => 'products/old.png', 'position' => 0]);
    $upload = UploadedFile::fake()->createWithContent('nova.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a9uoAAAAASUVORK5CYII='));
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))->put(route('admin.products.update', $product), pageProductData($product) + [
        'variants' => [pageVariantData($first, 0), pageVariantData($second, 8), ['size' => 'XL', 'color' => 'Preto', 'stock' => 2]],
        'removed_variants' => [$removed->id], 'removed_images' => [$image->id], 'image' => $upload, 'alt_text' => 'Nova fotografia',
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.products.index'));
    expect($product->fresh()->name)->toBe('Produto atualizado');
    expect($product->fresh()->price_cents)->toBe(4550);
    expect($first->fresh()->stock)->toBe(0);
    expect($second->fresh()->stock)->toBe(8);
    expect($second->fresh()->price_cents)->toBe(1250);
    expect($removed->fresh())->toBeNull();
    expect($product->variants()->count())->toBe(3);
    expect($product->images()->sole()->alt_text)->toBe('Nova fotografia');
    Storage::disk('public')->assertMissing('products/old.png');
    Storage::disk('public')->assertExists($product->images()->sole()->path);
    $this->get(route('admin.products.edit', $product))->assertOk()->assertDontSee('Carregar imagem')->assertDontSee('admin/variants/');
});

test('an invalid variant prevents saving every change on the page', function () {
    $variant = ProductVariant::factory()->create(['stock' => 4]);
    $product = $variant->product;
    $name = $product->name;
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))->put(route('admin.products.update', $product), pageProductData($product) + [
        'variants' => [pageVariantData($variant, -1)],
    ])->assertSessionHasErrors('variants.0.stock');
    expect($product->fresh()->name)->toBe($name);
    expect($variant->fresh()->stock)->toBe(4);
});

test('saving a product cannot modify or remove another products variants or images', function () {
    $product = Product::factory()->create();
    $foreign = ProductVariant::factory()->create(['stock' => 4]);
    $foreignImage = $foreign->product->images()->create(['path' => 'foreign.png', 'position' => 0]);
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))->put(route('admin.products.update', $product), pageProductData($product) + [
        'variants' => [pageVariantData($foreign, 9)], 'removed_variants' => [$foreign->id], 'removed_images' => [$foreignImage->id],
    ])->assertSessionHasErrors(['variants.0.id', 'removed_variants.0', 'removed_images.0']);
    expect($foreign->fresh()->stock)->toBe(4);
    expect($foreignImage->fresh())->not->toBeNull();
});

test('all variants can be removed explicitly without deleting omitted variants on ordinary updates', function () {
    $variant = ProductVariant::factory()->create();
    $product = $variant->product;
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
    $this->put(route('admin.products.update', $product), pageProductData($product))->assertSessionHasNoErrors();
    expect($variant->fresh())->not->toBeNull();
    $this->put(route('admin.products.update', $product), pageProductData($product) + ['removed_variants' => [$variant->id]])->assertSessionHasNoErrors();
    expect($product->variants()->count())->toBe(0);
});
