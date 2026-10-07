<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $guarded = ['id'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price_cents' => 'integer',
        ];
    }

    public function subtotalCents(): int
    {
        return $this->quantity * $this->unit_price_cents;
    }

    public function formattedUnitPrice(): string
    {
        return Money::format($this->unit_price_cents);
    }

    public function formattedSubtotal(): string
    {
        return Money::format($this->subtotalCents());
    }
}
