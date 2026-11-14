@extends('layouts.shop')

@section('title', __('Confirmar palavra-passe'))

@section('content')
    <section class="bg-stone-100">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-2 lg:items-center lg:gap-20 lg:px-8 lg:py-20">
            <div class="max-w-lg">
                <p class="text-sm uppercase tracking-widest text-gray-500">{{ __('Own the room') }}</p>
                <h1 class="mt-3 text-4xl font-semibold leading-tight sm:text-5xl">{{ __('A sua conta, em segurança.') }}</h1>
                <p class="mt-5 text-base leading-relaxed text-gray-600">{{ __('Para proteger os seus dados, precisamos de confirmar que é mesmo a pessoa que está a utilizar esta conta.') }}</p>
            </div>
            <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 sm:p-10 lg:justify-self-end">
                <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-full bg-stone-100 text-gray-900">
                    <svg aria-hidden="true" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="10" width="14" height="11" rx="2" />
                        <path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3" />
                    </svg>
                </div>
                <h2 class="text-2xl font-semibold">{{ __('Confirmar palavra-passe') }}</h2>
                <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ __('Introduza a sua palavra-passe atual para continuar.') }}</p>
                <form method="POST" action="{{ route('password.confirm') }}" class="mt-8 space-y-6">
                    @csrf
                    <div>
                        <x-input-label for="password" :value="__('Palavra-passe atual')" />
                        <x-password-toggle for="password">
                            <input id="password" class="block w-full rounded-lg border-gray-300 pr-12 focus:border-gray-900 focus:ring-gray-900" type="password" :type="visible ? 'text' : 'password'" name="password" required autofocus autocomplete="current-password" @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif>
                        </x-password-toggle>
                        <x-input-error id="password-error" :messages="$errors->get('password')" class="mt-2" />
                    </div>
                    <button type="submit" class="w-full rounded-full bg-gray-900 px-6 py-3 text-sm font-medium text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">{{ __('Confirmar e continuar') }}</button>
                </form>
                <p class="mt-8 border-t border-gray-100 pt-6 text-center text-sm text-gray-600">
                    <a href="{{ route('profile.edit') }}" class="rounded font-medium text-gray-900 underline underline-offset-4 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">{{ __('Voltar ao perfil') }}</a>
                </p>
            </div>
        </div>
    </section>
@endsection
