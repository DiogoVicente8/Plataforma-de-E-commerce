<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class GeneratedProductImagesSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::query()->whereDoesntHave('images')->get();

        foreach ($products as $product) {
            $filename = $product->slug.'.png';
            $source = resource_path('images/catalog/generated/'.$filename);

            if (! is_file($source)) {
                continue;
            }

            $path = 'catalog/generated/'.$filename;
            Storage::disk('public')->put($path, file_get_contents($source));

            $product->images()->firstOrCreate(['path' => $path], [
                'alt_text' => $product->name,
                'position' => 0,
            ]);
        }
    }
}
