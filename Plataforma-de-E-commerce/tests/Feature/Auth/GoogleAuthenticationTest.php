<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as GoogleUser;

function mockGoogleIdentity(bool $verified = true): void
{
    $identity = (new GoogleUser)->setRaw(['email_verified' => $verified])->map([
        'id' => 'google-123', 'name' => 'Cliente Google', 'email' => 'cliente@gmail.com',
    ]);
    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('user')->once()->andReturn($identity);
    Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);
}

test('google login handles missing configuration', function () {
    config(['services.google.client_id' => null]);
    $this->get(route('auth.google'))->assertRedirect(route('login'))->assertSessionHasErrors('google');
});

test('google login redirects to the provider', function () {
    config(['services.google.client_id' => 'client', 'services.google.client_secret' => 'secret']);
    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('with')->with(['prompt' => 'select_account'])->once()->andReturnSelf();
    $provider->shouldReceive('redirect')->once()->andReturn(redirect()->away('https://accounts.google.com/'));
    Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);
    $this->get(route('auth.google'))->assertRedirect('https://accounts.google.com/');
});

test('google login creates a verified customer', function () {
    mockGoogleIdentity();
    $this->get(route('auth.google.callback'))->assertRedirect(route('home', absolute: false));
    $user = User::where('google_id', 'google-123')->sole();
    $this->assertAuthenticatedAs($user);
    expect($user->hasVerifiedEmail())->toBeTrue()->and($user->isAdmin())->toBeFalse();
});

test('google login recognizes a linked account', function () {
    $user = User::factory()->create(['google_id' => 'google-123']);
    mockGoogleIdentity();
    $this->withSession(['url.intended' => route('profile.edit')])->get(route('auth.google.callback'))
        ->assertRedirect(route('profile.edit'));
    $this->assertAuthenticatedAs($user);
    expect(User::count())->toBe(1);
});

test('google registration sends a new customer home even with a saved destination', function () {
    mockGoogleIdentity();

    $this->withSession(['url.intended' => route('admin.dashboard')])
        ->get(route('auth.google.callback'))
        ->assertRedirect(route('home'))
        ->assertSessionMissing('url.intended');

    $this->assertAuthenticatedAs(User::where('google_id', 'google-123')->sole());
    $this->get(route('home'))->assertOk();
});

test('google login authenticates the selected identity among multiple linked accounts', function () {
    $otherUser = User::factory()->create(['google_id' => 'google-other']);
    $selectedUser = User::factory()->create(['google_id' => 'google-123', 'email' => 'cliente@gmail.com']);
    mockGoogleIdentity();

    $this->get(route('auth.google.callback'))->assertRedirect(route('home', absolute: false));

    $this->assertAuthenticatedAs($selectedUser);
    expect(auth()->id())->not->toBe($otherUser->id);
    expect(User::count())->toBe(2);
});

test('google login does not automatically link an existing email', function () {
    $user = User::factory()->create(['email' => 'cliente@gmail.com']);
    mockGoogleIdentity();
    $this->get(route('auth.google.callback'))->assertRedirect(route('login'))->assertSessionHasErrors('google');
    $this->assertGuest();
    expect($user->fresh()->google_id)->toBeNull();
});

test('google login rejects unverified email', function () {
    mockGoogleIdentity(false);
    $this->get(route('auth.google.callback'))->assertRedirect(route('login'))->assertSessionHasErrors('google');
    $this->assertGuest();
    expect(User::count())->toBe(0);
});

test('google login rejects invalid oauth state', function () {
    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('user')->once()->andThrow(new InvalidStateException);
    Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);
    $this->get(route('auth.google.callback'))->assertRedirect(route('login'))->assertSessionHasErrors('google');
    $this->assertGuest();
});

test('google login handles cancellation', function () {
    $this->get(route('auth.google.callback', ['error' => 'access_denied']))
        ->assertRedirect(route('login'))->assertSessionHasErrors('google');
    $this->assertGuest();
});

test('google customer can initiate account deletion without a password', function () {
    config(['services.google.client_id' => 'client', 'services.google.client_secret' => 'secret']);
    $user = User::factory()->create(['google_id' => 'google-123']);
    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('with')->with(['prompt' => 'select_account'])->once()->andReturnSelf();
    $provider->shouldReceive('redirect')->once()->andReturn(redirect()->away('https://accounts.google.com/'));
    Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

    $this->actingAs($user)->delete(route('profile.destroy'))
        ->assertRedirect('https://accounts.google.com/')
        ->assertSessionHas('google_account_deletion.user_id', $user->id);
    expect($user->fresh())->not->toBeNull();
});

test('google confirmation deletes only the initiating account and revokes tokens', function () {
    $user = User::factory()->create(['google_id' => 'google-123']);
    $other = User::factory()->create();
    $token = $user->createToken('test')->accessToken;
    mockGoogleIdentity();

    $this->actingAs($user)->withSession(['google_account_deletion' => [
        'user_id' => $user->id, 'expires_at' => now()->addMinutes(10)->timestamp,
    ]])->get(route('auth.google.callback'))->assertRedirect(route('home'));

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
    expect($token->fresh())->toBeNull();
    expect($other->fresh())->not->toBeNull();
});

test('google deletion rejects a different identity or expired confirmation', function (string $identity, bool $expired) {
    $user = User::factory()->create(['google_id' => $identity]);
    mockGoogleIdentity();

    $this->actingAs($user)->withSession(['google_account_deletion' => [
        'user_id' => $user->id,
        'expires_at' => $expired ? now()->subMinute()->timestamp : now()->addMinutes(10)->timestamp,
    ]])->get(route('auth.google.callback'))
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHasErrorsIn('userDeletion', 'google')
        ->assertSessionMissing('google_account_deletion');

    expect($user->fresh())->not->toBeNull();
    $this->assertAuthenticatedAs($user);
})->with([['another-google-id', false], ['google-123', true]]);

test('cancelling google deletion preserves the account', function () {
    $user = User::factory()->create(['google_id' => 'google-123']);

    $this->actingAs($user)->withSession(['google_account_deletion' => [
        'user_id' => $user->id, 'expires_at' => now()->addMinutes(10)->timestamp,
    ]])->get(route('auth.google.callback', ['error' => 'access_denied']))
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHasErrorsIn('userDeletion', 'google');

    expect($user->fresh())->not->toBeNull();
});

test('an unsolicited google callback does not switch or delete an authenticated account', function () {
    $user = User::factory()->create(['google_id' => 'google-123']);
    Socialite::shouldReceive('driver')->never();

    $this->actingAs($user)->get(route('auth.google.callback'))->assertRedirect(route('profile.edit'));

    expect($user->fresh())->not->toBeNull();
    $this->assertAuthenticatedAs($user);
});

test('google profile offers confirmation without a password field', function () {
    $user = User::factory()->create(['google_id' => 'google-123']);

    $this->actingAs($user)->get(route('profile.edit'))->assertOk()
        ->assertSee('Confirmar com Google e eliminar')
        ->assertDontSee('Introduz a tua palavra-passe para confirmar a eliminação');
});
