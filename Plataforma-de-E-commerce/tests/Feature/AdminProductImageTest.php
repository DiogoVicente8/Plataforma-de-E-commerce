<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function productCreationData(): array
{
    return ['category_id' => Category::factory()->create()->id, 'name' => 'Produto com imagem', 'price' => '29.95', 'is_active' => '1'];
}

function productPngUpload(): UploadedFile
{
    return UploadedFile::fake()->createWithContent('produto.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a9uoAAAAASUVORK5CYII='));
}

test('admins can create a product with a PNG as its main image', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
    $this->get(route('admin.products.create'))->assertOk()->assertSee('name="image"', false)->assertSee('multipart/form-data');
    $this->post(route('admin.products.store'), [...productCreationData(), 'image' => productPngUpload()])
        ->assertSessionHasNoErrors()->assertRedirect(route('admin.products.index'));
    $product = Product::with('images')->sole();
    expect($product->price_cents)->toBe(2995);
    expect($product->images)->toHaveCount(1);
    $image = $product->images->first();
    expect($image->position)->toBe(0);
    expect($image->alt_text)->toBe($product->name);
    expect($image->path)->toEndWith('.png');
    Storage::disk('public')->assertExists($image->path);
    $this->get(route('admin.products.edit', $product))->assertOk()->assertSee($image->path);
});

test('product image remains optional and editing existing data preserves it', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
    $data = productCreationData();
    $this->post(route('admin.products.store'), $data)->assertSessionHasNoErrors();
    $product = Product::sole();
    expect($product->images()->count())->toBe(0);
    $this->post(route('admin.products.images.store', $product), ['image' => productPngUpload()])->assertSessionHasNoErrors();
    $this->put(route('admin.products.update', $product), [...$data, 'name' => 'Nome atualizado'])->assertSessionHasNoErrors();
    expect($product->images()->count())->toBe(1);
});

test('invalid or oversized images cannot create products', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
    $data = productCreationData();
    $this->post(route('admin.products.store'), [...$data, 'image' => UploadedFile::fake()->createWithContent('falso.png', 'texto')->mimeType('text/plain')])
        ->assertSessionHasErrors('image');
    $this->post(route('admin.products.store'), [...$data, 'image' => productPngUpload()->size(2049)])
        ->assertSessionHasErrors('image');
    expect(Product::count())->toBe(0);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('customers cannot create products with image uploads', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create())->post(route('admin.products.store'), [...productCreationData(), 'image' => productPngUpload()])->assertForbidden();
    expect(Product::count())->toBe(0);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});
