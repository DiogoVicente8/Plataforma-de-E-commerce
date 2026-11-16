<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function apiUserWithRole(UserRole $role): User
{
    return User::factory()->create()->forceFill(['role' => $role]);
}

function apiProductPayload(?Category $category = null): array
{
    return [
        'category_id' => ($category ?? Category::factory()->create())->id,
        'name' => 'Camisola API',
        'slug' => 'camisola-api',
        'description' => 'Descrição da camisola.',
        'price_cents' => 2599,
        'is_active' => true,
    ];
}

it('lists only active products publicly with pagination metadata', function () {
    Product::factory()->count(16)->create();
    Product::factory()->create(['is_active' => false]);

    $this->getJson('/api/v1/products')
        ->assertOk()
        ->assertJsonCount(15, 'data')
        ->assertJsonPath('meta.per_page', 15)
        ->assertJsonPath('meta.total', 16);
});

it('shows an active product and returns json 404 for a missing product', function () {
    $product = Product::factory()->create();

    $this->getJson('/api/v1/products/'.$product->id)->assertOk()->assertJsonPath('data.id', $product->id);
    $this->getJson('/api/v1/products/999999')->assertNotFound()->assertJsonStructure(['message']);
});

it('requires authentication and admin role to create products', function () {
    $payload = apiProductPayload();

    $this->postJson('/api/v1/products', $payload)->assertUnauthorized();

    Sanctum::actingAs(apiUserWithRole(UserRole::Customer));
    $this->postJson('/api/v1/products', $payload)->assertForbidden()->assertJsonStructure(['message']);
});

it('allows admins to create products and validates input', function () {
    Sanctum::actingAs(apiUserWithRole(UserRole::Admin));

    $this->postJson('/api/v1/products', [])->assertUnprocessable()->assertJsonStructure(['message', 'errors']);

    $response = $this->postJson('/api/v1/products', apiProductPayload());
    $response->assertCreated()->assertJsonPath('data.slug', 'camisola-api')->assertHeader('Location');
    $this->assertDatabaseHas('products', ['slug' => 'camisola-api', 'price_cents' => 2599]);
});

it('allows admins to update and delete products', function () {
    Sanctum::actingAs(apiUserWithRole(UserRole::Admin));
    $product = Product::factory()->create();

    $this->patchJson('/api/v1/products/'.$product->id, ['name' => 'Nome atualizado'])
        ->assertOk()->assertJsonPath('data.name', 'Nome atualizado');

    $this->deleteJson('/api/v1/products/'.$product->id)->assertNoContent();
    $this->getJson('/api/v1/products/'.$product->id)->assertNotFound();
});
