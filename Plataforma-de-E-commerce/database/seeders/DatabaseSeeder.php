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
        $this->seedUser('Admin', 'admin@loja.test', UserRole::Admin);
        $this->seedUser('Cliente', 'cliente@loja.test', UserRole::Customer);

        $this->call(CatalogSeeder::class);
        $this->call(OrderSeeder::class);
    }

    private function seedUser(string $name, string $email, UserRole $role): User
    {
        return User::unguarded(fn (): User => User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => 'password', 'role' => $role],
        ));
    }
}
