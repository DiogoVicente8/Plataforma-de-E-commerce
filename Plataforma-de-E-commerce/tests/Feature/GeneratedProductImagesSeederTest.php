<?php

use App\Models\Product;
use Database\Seeders\GeneratedProductImagesSeeder;
use Illuminate\Support\Facades\Storage;

test('generated photos fill missing images without changing existing product data or photos', function () {
    Storage::fake('public');
    $missing = Product::factory()->create(['slug' => 'vestido-midi-floral', 'price_cents' => 1234]);
    $existing = Product::factory()->create(['slug' => 'blusa-de-cetim']);
    $existing->images()->create(['path' => 'custom/original.jpg', 'position' => 0]);
    $unknown = Product::factory()->create(['slug' => 'produto-personalizado']);

    $this->seed(GeneratedProductImagesSeeder::class);
    $this->seed(GeneratedProductImagesSeeder::class);

    expect($missing->images()->count())->toBe(1);
    expect($missing->images()->first()->path)->toBe('catalog/generated/vestido-midi-floral.png');
    Storage::disk('public')->assertExists('catalog/generated/vestido-midi-floral.png');
    expect($missing->fresh()->price_cents)->toBe(1234);
    expect($existing->images()->count())->toBe(1);
    expect($existing->images()->first()->path)->toBe('custom/original.jpg');
    expect($unknown->images()->count())->toBe(0);
});
