<?php

namespace App\Models;

use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'size', 'color', 'sku', 'stock', 'price_cents'])]
class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    public const SIZES = ['XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL'];

    // Posição do tamanho na lista; tamanhos fora da lista ficam no fim.
    public static function sizeOrder(string $size): int
    {
        $position = array_search($size, self::SIZES, true);

        return $position === false ? count(self::SIZES) : $position;
    }

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'price_cents' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}