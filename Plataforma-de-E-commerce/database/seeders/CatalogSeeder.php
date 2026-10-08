<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Vestidos' => [
                ['Vestido midi floral', 4590, ['Azul', 'Rosa']],
                ['Vestido de malha canelada', 3990, ['Preto', 'Bege']],
                ['Vestido camiseiro de linho', 5490, ['Branco']],
            ],
            'Blusas e Camisas' => [
                ['Blusa de cetim', 2990, ['Champanhe', 'Preto']],
                ['Camisa oversize às riscas', 3490, ['Azul']],
                ['Top de alças em malha', 1590, ['Branco', 'Preto']],
            ],
            'Calças e Saias' => [
                ['Calças de alfaiataria', 4290, ['Preto', 'Camel']],
                ['Calças de ganga wide leg', 3990, ['Azul']],
                ['Saia plissada midi', 3290, ['Verde', 'Preto']],
                ['Saia de ganga curta', 2490, ['Azul']],
            ],
            'Casacos' => [
                ['Blazer estruturado', 6990, ['Preto', 'Bege']],
                ['Casaco de malha comprido', 4990, ['Cinzento']],
                ['Trench coat clássico', 8990, ['Camel']],
            ],
            'Malhas' => [
                ['Camisola de gola alta', 2990, ['Creme', 'Preto']],
                ['Cardigan de botões', 3590, ['Rosa', 'Cinzento']],
                ['Colete de tricô', 2590, ['Bege']],
            ],
        ];

        foreach ($catalog as $categoryName => $products) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName, 'description' => 'Coleção de '.Str::lower($categoryName).'.'],
            );

            foreach ($products as [$name, $priceCents, $colors]) {
                $product = Product::updateOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'category_id' => $category->id,
                        'name' => $name,
                        'description' => $name.' da nova coleção.',
                        'price_cents' => $priceCents,
                        'is_active' => true,
                    ],
                );

                foreach ($colors as $color) {
                    foreach (['S', 'M', 'L'] as $size) {
                        $product->variants()->updateOrCreate(
                            ['size' => $size, 'color' => $color],
                            [
                                'sku' => sprintf('P%03d-%s-%s', $product->id, Str::upper(Str::slug($color)), $size),
                                'stock' => 10,
                            ],
                        );
                    }
                }
            }
        }
    }
}