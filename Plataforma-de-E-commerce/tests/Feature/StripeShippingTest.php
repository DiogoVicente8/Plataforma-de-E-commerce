<?php

use App\Models\User;
use App\Services\StripeCheckout;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
use Stripe\HttpClient\CurlClient;

it('charges the stored shipping amount in Stripe alongside the articles', function () {
    config(['cashier.secret' => 'sk_test_shipping']);
    $order = User::factory()->create()->orders()->create(['total_cents' => 2990, 'shipping_cents' => 490]);
    $order->items()->create(['product_name' => 'Artigo', 'quantity' => 2, 'unit_price_cents' => 1250]);
    $http = Mockery::mock(ClientInterface::class);
    $http->shouldReceive('request')->once()->withArgs(function ($method, $url, $headers, $params) {
        expect($params['line_items'][1]['price_data']['unit_amount'])->toBe(490)
            ->and($params['line_items'][1]['price_data']['product_data']['name'])->toBe('Portes de envio');
        $total = collect($params['line_items'])->sum(fn ($item) => $item['quantity'] * $item['price_data']['unit_amount']);
        expect($total)->toBe(2990);

        return true;
    })->andReturn(['{"id":"cs_shipping","object":"checkout.session","url":"https://checkout.stripe.com/test"}', 200, []]);
    ApiRequestor::setHttpClient($http);
    try {
        app(StripeCheckout::class)->create($order);
    } finally {
        ApiRequestor::setHttpClient(new CurlClient);
    }
});
