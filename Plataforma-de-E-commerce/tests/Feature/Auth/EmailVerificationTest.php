<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Mailer\Exception\TransportException;

test('verification resend handles mail transport failures without verifying the customer', function () {
    $user = User::factory()->unverified()->create();
    Notification::shouldReceive('send')->once()->andThrow(new TransportException('550 Too many emails per second'));

    $this->actingAs($user)->from(route('verification.notice'))
        ->post(route('verification.send'))
        ->assertRedirect(route('verification.notice'))
        ->assertSessionHasErrors([
            'email' => 'Não foi possível enviar o email de verificação neste momento. Aguarde um minuto e tente novamente.',
        ])
        ->assertSessionMissing('status');

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verification resend waits a minute between emails', function () {
    $user = User::factory()->unverified()->create();
    Notification::fake();
    $this->actingAs($user)->from(route('verification.notice'));

    $this->post(route('verification.send'))->assertSessionHas('status', 'verification-link-sent');
    $this->post(route('verification.send'))->assertRedirect(route('verification.notice'))
        ->assertSessionHasErrors('email');
    Notification::assertSentToTimes($user, VerifyEmail::class, 1);

    $this->travel(61)->seconds();
    $this->post(route('verification.send'))->assertSessionHas('status', 'verification-link-sent');
    Notification::assertSentToTimes($user, VerifyEmail::class, 2);
});

test('unverified customers must verify before continuing to browse or checkout', function () {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);

    foreach (['home', 'shop.index', 'cart.index', 'checkout.create'] as $route) {
        $this->get(route($route))->assertRedirect(route('verification.notice'));
    }

    $this->get(route('verification.notice'))->assertOk();
    Notification::fake();
    $this->post(route('verification.send'))->assertSessionHas('status', 'verification-link-sent');
    Notification::assertSentTo($user, VerifyEmail::class);
    $this->post(route('logout'))->assertRedirect('/');
    $this->assertGuest();
});

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/verify-email');

    $response->assertStatus(200);
});

test('email can be verified', function () {
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('home', absolute: false).'?verified=1');
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});
