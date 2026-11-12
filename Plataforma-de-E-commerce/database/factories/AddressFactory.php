<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => 'Casa',
            'recipient_name' => fake()->name(),
            'phone_country' => 'PT',
            'phone' => '912345678',
            'street' => fake()->streetAddress(),
            'postal_code' => '4440-123',
            'city' => 'Valongo',
            'is_default' => false,
        ];
    }
}
