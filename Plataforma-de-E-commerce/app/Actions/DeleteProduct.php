<?php

namespace App\Actions;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class DeleteProduct
{
    public function execute(Product $product): void
    {
        Storage::disk('public')->delete($product->images()->pluck('path')->all());
        $product->delete();
    }
}
