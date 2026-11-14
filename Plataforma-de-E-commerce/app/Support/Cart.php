<?php

namespace App\Support;

use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class Cart
{
    public static function count(): int
    {
        $cart = session('cart', []);

        if ($cart === []) {
            return 0;
        }

        return (int) ProductVariant::query()
            ->whereIn('id', array_keys($cart))
            ->whereHas('product', fn ($query) => $query->active())
            ->get(['id', 'stock'])
            ->sum(fn (ProductVariant $variant) => min((int) $cart[$variant->id], $variant->stock));
    }

    // Linhas do carrinho guardado na sessão, com preço e stock atuais da base de dados.
    public static function items(): Collection
    {
        $cart = session('cart', []);

        if ($cart === []) {
            return collect();
        }

        return ProductVariant::query()
            ->with('product.images')
            ->whereIn('id', array_keys($cart))
            ->whereHas('product', fn ($query) => $query->active())
            ->get()
            ->map(function (ProductVariant $variant) use ($cart) {
                $quantity = min((int) $cart[$variant->id], $variant->stock);
                $price = $variant->price_cents ?? $variant->product->price_cents;

                return ['variant' => $variant, 'quantity' => $quantity, 'price' => $price, 'subtotal' => $price * $quantity];
            });
    }
}
