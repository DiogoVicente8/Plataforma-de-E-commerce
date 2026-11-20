<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return to_route('login')->withErrors(['google' => 'O login com Google ainda não está disponível.']);
        }

        return Socialite::driver('google')->with(['prompt' => 'select_account'])->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $deletion = $request->session()->pull('google_account_deletion');
        $failureRoute = $deletion ? 'profile.edit' : 'login';
        $errorBag = $deletion ? 'userDeletion' : 'default';

        if ($request->user() && ! $deletion) {
            return to_route('profile.edit');
        }

        if ($request->has('error')) {
            return to_route($failureRoute)->withErrors(['google' => 'A confirmação com Google foi cancelada.'], $errorBag);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Exception $exception) {
            report($exception);

            return to_route($failureRoute)->withErrors(['google' => 'Não foi possível confirmar a conta Google. Tenta novamente.'], $errorBag);
        }

        if (! $googleUser->getId() || ! filter_var($googleUser->getEmail(), FILTER_VALIDATE_EMAIL)
            || ($googleUser->user['email_verified'] ?? false) !== true) {
            return to_route($failureRoute)->withErrors(['google' => 'A conta Google precisa de um email verificado.'], $errorBag);
        }

        if ($deletion) {
            $user = $request->user();

            if (! $user || $deletion['user_id'] !== $user->id || $deletion['expires_at'] < now()->timestamp
                || ! $user->google_id || (string) $user->google_id !== (string) $googleUser->getId()) {
                return to_route($user ? 'profile.edit' : 'login')->withErrors([
                    'google' => 'A conta não foi eliminada. Escolhe a conta Google associada ao teu perfil e confirma novamente.',
                ], 'userDeletion');
            }

            DB::transaction(function () use ($user) {
                Auth::logout();
                $user->tokens()->delete();
                DB::table('sessions')->where('user_id', $user->id)->delete();
                DB::table('password_reset_tokens')->where('email', $user->email)->delete();
                $user->delete();
            });

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return to_route('home');
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        $isNewUser = ! $user;

        if ($isNewUser) {
            if (User::where('email', $googleUser->getEmail())->exists()) {
                return to_route('login')->withErrors(['google' => 'Já existe uma conta com este email. Entra com a tua palavra-passe.']);
            }

            $user = new User([
                'name' => $googleUser->getName() ?: 'Cliente',
                'email' => $googleUser->getEmail(),
                'password' => Str::random(64),
            ]);
            $user->google_id = $googleUser->getId();
            $user->email_verified_at = now();
            $user->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->isAdmin()) {
            $request->session()->forget('url.intended');

            return to_route('admin.dashboard');
        }

        if ($isNewUser) {
            $request->session()->forget('url.intended');

            return to_route('home');
        }

        return redirect()->intended(route('home', absolute: false));
    }
}
