<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoCatalogSeeder extends Seeder
{
    protected function catalogPath(): string
    {
        return database_path('seeders/data/zara-catalog.json');
    }

    public function run(): void
    {
        $catalog = json_decode(file_get_contents($this->catalogPath()), true, flags: JSON_THROW_ON_ERROR);

        foreach ($catalog as $entry) {
            $path = 'catalog/'.$entry['image'];
            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->put($path, file_get_contents(resource_path('images/catalog/'.$entry['image'])));
            }

            DB::transaction(function () use ($entry, $path): void {
                $category = Category::firstOrCreate(['slug' => Str::slug($entry['category'])], ['name' => $entry['category']]);
                $product = Product::firstOrCreate(['slug' => Str::slug($entry['name'])], [
                    'category_id' => $category->id,
                    'name' => $entry['name'],
                    'description' => $entry['description'],
                    'price_cents' => $entry['price_cents'],
                    'is_active' => true,
                ]);

                foreach ($entry['stock'] as $size => $stock) {
                    // Re-running the seeder preserves stock edited in the administration panel.
                    $product->variants()->firstOrCreate(['size' => $size, 'color' => $entry['color']], [
                        'sku' => sprintf('PHOTO-P%03d-%s', $product->id, $size),
                        'stock' => $stock,
                    ]);
                }

                $product->images()->firstOrCreate(['path' => $path], [
                    'alt_text' => $entry['name'].' — '.$entry['color'],
                    'position' => 0,
                ]);
            });
        }
    }
}
