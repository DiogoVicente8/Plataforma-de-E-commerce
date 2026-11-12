@extends('layouts.shop')

@section('title', __('Criar conta'))

@section('content')
    <section class="bg-stone-100">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-2 lg:items-center lg:gap-20 lg:px-8 lg:py-20">
            <div class="max-w-lg">
                <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
                    <span aria-hidden="true">&larr;</span> {{ __('Voltar à coleção') }}
                </a>
                <p class="mt-8 text-sm uppercase tracking-widest text-gray-500">{{ __('Own the room') }}</p>
                <h1 class="mt-3 text-4xl font-semibold leading-tight sm:text-5xl">{{ __('Dê espaço ao seu estilo.') }}</h1>
                <p class="mt-5 text-base leading-relaxed text-gray-600">{{ __('Crie a sua conta e acompanhe as suas encomendas. Descubra peças feitas para a acompanhar todos os dias.') }}</p>
            </div>

            <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 sm:p-10 lg:justify-self-end">
                <h2 class="text-2xl font-semibold">{{ __('Criar conta') }}</h2>
                <p class="mt-2 text-sm text-gray-600">{{ __('Preencha os seus dados para começar.') }}</p>

                <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-6">
                    @csrf
                    <div>
                        <x-input-label for="name" :value="__('Nome')" />
                        <input id="name" class="mt-2 block w-full rounded-lg border-gray-300 focus:border-gray-900 focus:ring-gray-900" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" @if ($errors->has('name')) aria-invalid="true" aria-describedby="name-error" @endif />
                        <x-input-error id="name-error" :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <input id="email" class="mt-2 block w-full rounded-lg border-gray-300 focus:border-gray-900 focus:ring-gray-900" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif />
                        <x-input-error id="email-error" :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="password" :value="__('Palavra-passe')" />
                        <input id="password" class="mt-2 block w-full rounded-lg border-gray-300 focus:border-gray-900 focus:ring-gray-900" type="password" name="password" required autocomplete="new-password" @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif />
                        <x-input-error id="password-error" :messages="$errors->get('password')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirmar palavra-passe')" />
                        <input id="password_confirmation" class="mt-2 block w-full rounded-lg border-gray-300 focus:border-gray-900 focus:ring-gray-900" type="password" name="password_confirmation" required autocomplete="new-password" @if ($errors->has('password_confirmation')) aria-invalid="true" aria-describedby="password-confirmation-error" @endif />
                        <x-input-error id="password-confirmation-error" :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>
                    <button type="submit" class="w-full rounded-full bg-gray-900 px-6 py-3 text-sm font-medium text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">{{ __('Criar conta') }}</button>
                </form>

                <p class="mt-8 border-t border-gray-100 pt-6 text-center text-sm text-gray-600">
                    {{ __('Já tem conta?') }}
                    <a href="{{ route('login') }}" class="rounded font-medium text-gray-900 underline underline-offset-4 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">{{ __('Entrar') }}</a>
                </p>
            </div>
        </div>
    </section>
@endsection
