<?php

namespace App\Support;

class Shipping
{
    public static function cost(int $subtotal): int
    {
        $freeFrom = (int) config('shipping.free_from_cents');

        if ($subtotal <= 0 || ($freeFrom > 0 && $subtotal >= $freeFrom)) {
            return 0;
        }

        return max(0, (int) config('shipping.cost_cents'));
    }
}
