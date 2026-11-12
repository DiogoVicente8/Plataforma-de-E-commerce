@extends('layouts.shop')

@section('title', __('Entrar'))

@section('content')
    <section class="bg-stone-100">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-2 lg:items-center lg:gap-20 lg:px-8 lg:py-20">
            <div class="max-w-lg">
                <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
                    <span aria-hidden="true">&larr;</span> {{ __('Voltar à coleção') }}
                </a>
                <p class="mt-8 text-sm uppercase tracking-widest text-gray-500">{{ __('Own the room') }}</p>
                <h1 class="mt-3 text-4xl font-semibold leading-tight sm:text-5xl">{{ __('O seu estilo começa aqui.') }}</h1>
                <p class="mt-5 text-base leading-relaxed text-gray-600">{{ __('Entre na sua conta e acompanhe as suas encomendas. A sua próxima peça favorita está à sua espera.') }}</p>
            </div>

            <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 sm:p-10 lg:justify-self-end">
                <h2 class="text-2xl font-semibold">{{ __('Bem-vinda de volta') }}</h2>
                <p class="mt-2 text-sm text-gray-600">{{ __('Entre com os dados da sua conta.') }}</p>
                <x-auth-session-status class="mt-6" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-6">
                    @csrf
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <input id="email" class="mt-2 block w-full rounded-lg border-gray-300 focus:border-gray-900 focus:ring-gray-900" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif />
                        <x-input-error id="email-error" :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="password" :value="__('Palavra-passe')" />
                        <div class="relative mt-2" x-data="{ showPassword: false }">
                            <input id="password" class="block w-full rounded-lg border-gray-300 pr-12 focus:border-gray-900 focus:ring-gray-900" type="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif />
                            <button type="button" x-cloak @click="showPassword = !showPassword" aria-controls="password" :aria-pressed="showPassword.toString()" :aria-label="showPassword ? @js(__('Ocultar palavra-passe')) : @js(__('Mostrar palavra-passe'))" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-lg text-gray-500 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gray-900">
                                <svg x-show="!showPassword" aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12s3.5-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.5 7.5-9.75 7.5S2.25 12 2.25 12Z" />
                                    <circle cx="12" cy="12" r="3" stroke-width="1.5" />
                                </svg>
                                <svg x-show="showPassword" aria-hidden="true" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m3 3 18 18M10.6 4.6c.5-.07.95-.1 1.4-.1 6.25 0 9.75 7.5 9.75 7.5a20 20 0 0 1-3 4.2M6.1 6.1A22 22 0 0 0 2.25 12s3.5 7.5 9.75 7.5c1.9 0 3.55-.7 4.95-1.7M9.9 9.9a3 3 0 0 0 4.2 4.2" />
                                </svg>
                            </button>
                        </div>
                        <x-input-error id="password-error" :messages="$errors->get('password')" class="mt-2" />
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-4 text-sm">
                        <label for="remember_me" class="inline-flex items-center gap-2 text-gray-600">
                            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-gray-900 focus:ring-gray-900" name="remember" @checked(old('remember')) />
                            <span>{{ __('Lembrar-me') }}</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a class="rounded underline underline-offset-4 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2" href="{{ route('password.request') }}">{{ __('Esqueceu-se da palavra-passe?') }}</a>
                        @endif
                    </div>
                    <button type="submit" class="w-full rounded-full bg-gray-900 px-6 py-3 text-sm font-medium text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">{{ __('Entrar') }}</button>
                </form>

                @if (Route::has('register'))
                    <p class="mt-8 border-t border-gray-100 pt-6 text-center text-sm text-gray-600">
                        {{ __('Ainda não tem conta?') }}
                        <a href="{{ route('register') }}" class="rounded font-medium text-gray-900 underline underline-offset-4 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">{{ __('Criar conta') }}</a>
                    </p>
                @endif
            </div>
        </div>
    </section>
@endsection
