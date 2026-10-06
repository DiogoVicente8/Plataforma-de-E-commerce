<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@loja.test'],
            ['name' => 'Admin', 'password' => 'password', 'role' => UserRole::Admin],
        );

        User::firstOrCreate(
            ['email' => 'cliente@loja.test'],
            ['name' => 'Cliente', 'password' => 'password', 'role' => UserRole::Customer],
        );

        $this->call(OrderSeeder::class);
    }
}
