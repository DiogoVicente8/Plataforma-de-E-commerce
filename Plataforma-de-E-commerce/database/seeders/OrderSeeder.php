<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'cliente@loja.test')->firstOrFail();

        $category = Category::firstOrCreate(
            ['slug' => 'demo'],
            [
                'name' => 'Demonstração',
                'description' => 'Categoria criada para os dados de demonstração.',
            ],
        );

        $products = collect([
            [
                'slug' => 't-shirt-demo',
                'name' => 'T-shirt de demonstração',
                'price_cents' => 1999,
            ],
            [
                'slug' => 'caneca-demo',
                'name' => 'Caneca de demonstração',
                'price_cents' => 1299,
            ],
            [
                'slug' => 'saco-demo',
                'name' => 'Saco de demonstração',
                'price_cents' => 899,
            ],
        ])->mapWithKeys(function (array $attributes) use ($category) {
            $product = Product::firstOrCreate(
                ['slug' => $attributes['slug']],
                [
                    'category_id' => $category->id,
                    'name' => $attributes['name'],
                    'description' => 'Produto criado para as encomendas de demonstração.',
                    'price_cents' => $attributes['price_cents'],
                    'is_active' => true,
                ],
            );

            return [$attributes['slug'] => $product];
        });

        $samples = [
            'pending' => [
                ['product' => 't-shirt-demo', 'quantity' => 2],
                ['product' => 'caneca-demo', 'quantity' => 1],
            ],
            'paid' => [
                ['product' => 'saco-demo', 'quantity' => 1],
                ['product' => 'caneca-demo', 'quantity' => 2],
            ],
            'shipped' => [
                ['product' => 't-shirt-demo', 'quantity' => 1],
                ['product' => 'saco-demo', 'quantity' => 2],
            ],
        ];

        foreach ($samples as $status => $lines) {
            $order = Order::firstOrCreate(
                ['user_id' => $customer->id, 'status' => $status],
                ['total_cents' => 0],
            );

            foreach ($lines as $line) {
                $product = $products->get($line['product']);

                $order->items()->updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'product_name' => $product->name,
                        'quantity' => $line['quantity'],
                        'unit_price_cents' => $product->price_cents,
                    ],
                );
            }

            $totalCents = $order->items()
                ->get()
                ->sum(fn ($item) => $item->quantity * $item->unit_price_cents);

            $order->update(['total_cents' => $totalCents]);
        }
    }
}
