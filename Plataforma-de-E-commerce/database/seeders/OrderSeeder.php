<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'cliente@loja.test')->firstOrFail();

        $products = Product::query()->where('is_active', true)->orderBy('id')->take(3)->get();

        if ($products->count() < 3) {
            $products = $this->createDemoProducts();
        }

        $samples = [OrderStatus::Pending, OrderStatus::Paid, OrderStatus::Shipped];

        foreach ($samples as $index => $status) {
            $order = Order::updateOrCreate(
                ['user_id' => $customer->id, 'status' => $status->value],
                ['total_cents' => 0],
            );

            $lines = [
                ['product' => $products[$index], 'quantity' => $index + 1],
                ['product' => $products[($index + 1) % 3], 'quantity' => 1],
            ];

            foreach ($lines as $line) {
                $product = $line['product'];

                $order->items()->updateOrCreate(
                    ['product_id' => $product->id],
                    [
                        'product_name' => $product->name,
                        'quantity' => $line['quantity'],
                        'unit_price_cents' => $product->price_cents,
                    ],
                );
            }

            $order->update([
                'total_cents' => $order->items->sum(fn ($item): int => $item->subtotalCents()),
            ]);
        }
    }

    /** @return Collection<int, Product> */
    private function createDemoProducts(): Collection
    {
        $category = Category::firstOrCreate(
            ['slug' => 'demo'],
            ['name' => 'Demonstração', 'description' => 'Categoria criada para os dados de demonstração.'],
        );

        $definitions = [
            ['slug' => 't-shirt-basica-demo', 'name' => 'T-shirt básica', 'price_cents' => 1999],
            ['slug' => 'camisola-la-demo', 'name' => 'Camisola de lã', 'price_cents' => 3990],
            ['slug' => 'calcas-ganga-demo', 'name' => 'Calças de ganga', 'price_cents' => 4990],
        ];

        foreach ($definitions as $attributes) {
            Product::firstOrCreate(
                ['slug' => $attributes['slug']],
                [
                    'category_id' => $category->id,
                    'name' => $attributes['name'],
                    'description' => 'Produto criado para as encomendas de demonstração.',
                    'price_cents' => $attributes['price_cents'],
                    'is_active' => true,
                ],
            );
        }

        return Product::query()->whereIn('slug', array_column($definitions, 'slug'))->orderBy('id')->get();
    }
}
