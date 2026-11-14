@extends('layouts.shop')

@section('title', __('Nova palavra-passe'))

@section('content')
    <section class="bg-stone-100">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-2 lg:items-center lg:gap-20 lg:px-8 lg:py-20">
            <div class="max-w-lg">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded text-sm text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
                    <span aria-hidden="true">&larr;</span> {{ __('Voltar a entrar') }}
                </a>
                <p class="mt-8 text-sm uppercase tracking-widest text-gray-500">{{ __('Own the room') }}</p>
                <h1 class="mt-3 text-4xl font-semibold leading-tight sm:text-5xl">{{ __('Um novo acesso ao seu estilo.') }}</h1>
                <p class="mt-5 text-base leading-relaxed text-gray-600">{{ __('Escolha uma nova palavra-passe para voltar à sua conta B-Su e continuar a acompanhar as suas encomendas.') }}</p>
            </div>
            <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 sm:p-10 lg:justify-self-end">
                <h2 class="text-2xl font-semibold">{{ __('Definir nova palavra-passe') }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ __('Confirme o seu email e introduza a nova palavra-passe nos dois campos.') }}</p>
                <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-6">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <input id="email" class="mt-2 block w-full rounded-lg border-gray-300 focus:border-gray-900 focus:ring-gray-900" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif>
                        <x-input-error id="email-error" :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="password" :value="__('Nova palavra-passe')" />
                        <x-password-toggle for="password">
                            <input id="password" class="block w-full rounded-lg border-gray-300 pr-12 focus:border-gray-900 focus:ring-gray-900" type="password" :type="visible ? 'text' : 'password'" name="password" required autocomplete="new-password" @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif>
                        </x-password-toggle>
                        <x-input-error id="password-error" :messages="$errors->get('password')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirmar nova palavra-passe')" />
                        <x-password-toggle for="password_confirmation">
                            <input id="password_confirmation" class="block w-full rounded-lg border-gray-300 pr-12 focus:border-gray-900 focus:ring-gray-900" type="password" :type="visible ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" @if ($errors->has('password_confirmation')) aria-invalid="true" aria-describedby="password-confirmation-error" @endif>
                        </x-password-toggle>
                        <x-input-error id="password-confirmation-error" :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>
                    <button type="submit" class="w-full rounded-full bg-gray-900 px-6 py-3 text-sm font-medium text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">{{ __('Guardar nova palavra-passe') }}</button>
                </form>
                <p class="mt-8 border-t border-gray-100 pt-6 text-center text-sm text-gray-600">
                    {{ __('Precisa de um novo link?') }}
                    <a href="{{ route('password.request') }}" class="rounded font-medium text-gray-900 underline underline-offset-4 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">{{ __('Recuperar palavra-passe') }}</a>
                </p>
            </div>
        </div>
    </section>
@endsection
