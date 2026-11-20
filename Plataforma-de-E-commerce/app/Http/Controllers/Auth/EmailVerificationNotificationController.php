<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('home', absolute: false));
        }

        $key = 'verification-email:'.$request->user()->getAuthIdentifier();

        if (RateLimiter::tooManyAttempts($key, 1)) {
            return back()->withErrors([
                'email' => 'Aguarde '.RateLimiter::availableIn($key).' segundos antes de pedir outro email de verificação.',
            ]);
        }

        RateLimiter::hit($key, 60);

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return back()->withErrors([
                'email' => 'Não foi possível enviar o email de verificação neste momento. Aguarde um minuto e tente novamente.',
            ]);
        }

        return back()->with('status', 'verification-link-sent');
    }
}
