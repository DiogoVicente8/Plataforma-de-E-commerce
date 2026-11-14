@extends('layouts.shop')

@section('title', __('Recuperar palavra-passe'))

@section('content')
    <section class="bg-stone-100">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-2 lg:items-center lg:gap-20 lg:px-8 lg:py-20">
            <div class="max-w-lg">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded text-sm text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
                    <span aria-hidden="true">&larr;</span> {{ __('Voltar a entrar') }}
                </a>
                <p class="mt-8 text-sm uppercase tracking-widest text-gray-500">{{ __('Own the room') }}</p>
                <h1 class="mt-3 text-4xl font-semibold leading-tight sm:text-5xl">{{ __('Volte ao seu espaço.') }}</h1>
                <p class="mt-5 text-base leading-relaxed text-gray-600">{{ __('Esqueceu-se da palavra-passe? Recupere o acesso à sua conta para continuar a descobrir a coleção e acompanhar as suas encomendas.') }}</p>
            </div>
            <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 sm:p-10 lg:justify-self-end">
                <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-full bg-stone-100 text-gray-900">
                    <svg aria-hidden="true" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <path d="m3 7 9 6 9-6" />
                    </svg>
                </div>
                <h2 class="text-2xl font-semibold">{{ __('Recuperar palavra-passe') }}</h2>
                <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ __('Introduza o email associado à sua conta. Enviaremos um link para definir uma nova palavra-passe.') }}</p>
                @if (session('status'))
                    <div role="status" class="mt-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                        {{ session('status') === 'passwords.sent' ? __('Enviámos um link para recuperar a sua palavra-passe. Consulte a caixa de entrada e a pasta de spam.') : __(session('status')) }}
                    </div>
                @endif
                <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-6">
                    @csrf
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <input id="email" class="mt-2 block w-full rounded-lg border-gray-300 focus:border-gray-900 focus:ring-gray-900" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif>
                        <x-input-error id="email-error" :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <button type="submit" class="w-full rounded-full bg-gray-900 px-6 py-3 text-sm font-medium text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">{{ __('Enviar link de recuperação') }}</button>
                </form>
                <p class="mt-8 border-t border-gray-100 pt-6 text-center text-sm text-gray-600">
                    {{ __('Lembrou-se da palavra-passe?') }}
                    <a href="{{ route('login') }}" class="rounded font-medium text-gray-900 underline underline-offset-4 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">{{ __('Entrar') }}</a>
                </p>
            </div>
        </div>
    </section>
@endsection
