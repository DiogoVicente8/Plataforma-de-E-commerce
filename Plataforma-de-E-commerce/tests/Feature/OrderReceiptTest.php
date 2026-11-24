<?php

use App\Enums\OrderStatus;
use App\Models\User;

beforeEach(function () {
    $this->customer = User::factory()->create();
    $this->order = $this->customer->orders()->create([
        'status' => OrderStatus::Paid,
        'total_cents' => 2500,
        'shipping_address' => "Ana Silva\nRua de São João, 10\nPorto",
    ]);
    $this->order->items()->create([
        'product_name' => 'Vestido de verão (S · Azul)', 'quantity' => 2, 'unit_price_cents' => 1250,
    ]);
});

it('downloads a real PDF for the owner of a paid order', function () {
    $response = $this->actingAs($this->customer)->get(route('account.orders.receipt', $this->order));
    $response->assertOk()->assertHeader('Content-Type', 'application/pdf')
        ->assertDownload('comprovativo-encomenda-'.$this->order->id.'.pdf');
    expect($response->getContent())->toStartWith('%PDF-');
    $this->get(route('account.orders.show', $this->order))->assertSee('Descarregar comprovativo (PDF)');
});

it('rejects downloads by other customers', function () {
    $this->actingAs(User::factory()->create())->get(route('account.orders.receipt', $this->order))->assertForbidden();
});

it('requires login to download receipts', function () {
    $this->get(route('account.orders.receipt', $this->order))->assertRedirect(route('login'));
});

it('does not issue receipts for unpaid or cancelled orders', function (OrderStatus $status) {
    $this->order->update(['status' => $status]);
    $this->actingAs($this->customer)->get(route('account.orders.receipt', $this->order))->assertForbidden();
    $this->get(route('account.orders.show', $this->order))->assertDontSee('Descarregar comprovativo (PDF)');
})->with([OrderStatus::Pending, OrderStatus::Cancelled]);

it('includes the purchased items and delivery details in the receipt template', function () {
    $html = view('account.orders.receipt', ['order' => $this->order->load(['user', 'items'])])->render();
    expect($html)->toContain('Vestido de verão', 'Ana Silva', 'São João', '25,00', '12,50');
});
