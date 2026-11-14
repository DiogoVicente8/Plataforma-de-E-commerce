<?php

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    // Nos testes nunca se contacta o serviço real: 4440-123 existe, 0000-000 não.
    Http::fake([
        '*/cp/4440-123' => Http::response(['CP' => '4440-123', 'Localidade' => 'Campo']),
        '*/cp/0000-000' => Http::response(['erro' => 'Postal Code not found!'], 404),
    ]);
});

it('creates the first address as the default one', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)->post(route('account.addresses.store'), [
        'recipient_name' => 'Ana Silva',
        'phone_country' => 'PT',
        'phone' => '912 345 678',
        'street' => 'Rua de Valongo, 10',
        'postal_code' => '4440-123',
        'city' => 'Valongo',
    ])->assertRedirect(route('account.addresses.index'));

    expect($customer->addresses()->sole())
        ->phone->toBe('912345678')
        ->is_default->toBeTrue();
});

it('keeps only one default address', function () {
    $customer = User::factory()->create();
    $first = Address::factory()->for($customer)->create(['is_default' => true]);
    $second = Address::factory()->for($customer)->create();

    $this->actingAs($customer)->put(route('account.addresses.update', $second), [
        ...$second->only(['recipient_name', 'phone_country', 'phone', 'street', 'postal_code', 'city']),
        'is_default' => '1',
    ]);

    expect($first->fresh()->is_default)->toBeFalse()
        ->and($second->fresh()->is_default)->toBeTrue();
});

it('validates the phone number according to the chosen country', function () {
    $customer = User::factory()->create();
    $address = ['recipient_name' => 'Ana Silva', 'street' => 'Rua de Valongo, 10', 'postal_code' => '4440-123', 'city' => 'Valongo'];

    $this->actingAs($customer)
        ->post(route('account.addresses.store'), [...$address, 'phone_country' => 'PT', 'phone' => '061234567'])
        ->assertSessionHasErrors('phone');

    $this->actingAs($customer)
        ->post(route('account.addresses.store'), [...$address, 'phone_country' => 'FR', 'phone' => '061234567'])
        ->assertSessionHasNoErrors();
});

it('rejects an invalid postal code', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('account.addresses.store'), ['postal_code' => '4440123'])
        ->assertSessionHasErrors('postal_code');
});

it('does not let a customer edit or delete another customers address', function () {
    $address = Address::factory()->create();
    $intruder = User::factory()->create();

    $this->actingAs($intruder)->get(route('account.addresses.edit', $address))->assertForbidden();
    $this->actingAs($intruder)->delete(route('account.addresses.destroy', $address))->assertForbidden();

    expect($address->fresh())->not->toBeNull();
});

it('fills in the city from the postal code when it is left empty', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)->post(route('account.addresses.store'), [
        'recipient_name' => 'Ana Silva',
        'phone_country' => 'PT',
        'phone' => '912345678',
        'street' => 'Rua dos Académicos, 10',
        'postal_code' => '4440-123',
    ])->assertSessionHasNoErrors();

    expect($customer->addresses()->sole()->city)->toBe('Campo');
});

it('asks for the city when the postal code service cannot give it', function (string $postalCode) {
    Http::fake(['*/cp/4000-001' => fn () => throw new ConnectionException('Timeout')]);
    Log::spy();

    $this->actingAs(User::factory()->create())
        ->post(route('account.addresses.store'), [
            'recipient_name' => 'Ana Silva',
            'phone_country' => 'PT',
            'phone' => '912345678',
            'street' => 'Rua de Valongo, 10',
            'postal_code' => $postalCode,
        ])
        ->assertSessionHasErrors('city');

    // Só a falha do serviço fica registada; um código desconhecido não é uma falha.
    if ($postalCode === '4000-001') {
        Log::shouldHaveReceived('warning')->once();
    } else {
        Log::shouldNotHaveReceived('warning');
    }
})->with(['unknown postal code' => '0000-000', 'service down' => '4000-001']);
