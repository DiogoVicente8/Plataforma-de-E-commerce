<?php

use App\Models\Address;
use App\Models\User;

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
