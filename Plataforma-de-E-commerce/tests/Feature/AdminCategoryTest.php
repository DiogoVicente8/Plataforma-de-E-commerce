<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => UserRole::Admin]);
});

it('lets an administrator create a category with a generated slug', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.categories.store'), ['name' => 'Vestidos de Verão'])
        ->assertRedirect(route('admin.categories.index'));

    expect(Category::where('slug', 'vestidos-de-verao')->exists())->toBeTrue();
});

it('does not delete a category that still has products', function () {
    $category = Product::factory()->create()->category;

    $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));

    expect($category->fresh())->not->toBeNull();
});

it('deletes an empty category', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));

    expect($category->fresh())->toBeNull();
});

it('forbids customers from managing categories', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.categories.index'))
        ->assertForbidden();
});
