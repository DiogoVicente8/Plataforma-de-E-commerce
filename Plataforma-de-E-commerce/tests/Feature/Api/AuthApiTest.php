<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

it('logs in with valid credentials and rejects invalid credentials', function () {
    $user = User::factory()->create(['email' => 'api@example.test', 'password' => 'password']);
    DB::table('users')->where('id', $user->id)->update(['role' => UserRole::Admin->value]);
    $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => UserRole::Admin->value]);

    $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'password'])
        ->assertOk()
        ->assertJsonPath('user.email', $user->email)
        ->assertJsonPath('user.role', 'admin')
        ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);

    $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'incorreta'])
        ->assertUnprocessable()->assertJsonStructure(['message', 'errors']);
});

it('returns the authenticated user and revokes the current token on logout', function () {
    $user = User::factory()->create();
    $user->forceFill(['role' => UserRole::Customer])->save();
    $token = $user->createToken('teste')->plainTextToken;

    $this->withToken($token)->getJson('/api/v1/auth/me')->assertOk()->assertJsonPath('user.id', $user->id);
    $this->withToken($token)->postJson('/api/v1/auth/logout')->assertNoContent();
    $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);

    Auth::forgetGuards();
    $this->withToken($token)->getJson('/api/v1/auth/me')->assertUnauthorized();
});
