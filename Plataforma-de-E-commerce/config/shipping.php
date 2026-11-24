<?php

return [
    'cost_cents' => (int) env('SHIPPING_COST_CENTS', 490),
    'free_from_cents' => (int) env('SHIPPING_FREE_FROM_CENTS', 7500),
];
