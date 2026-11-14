@extends('layouts.shop')

@section('title', __('Verificar email'))

@section('content')
    <section class="bg-stone-100">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-2 lg:items-center lg:gap-20 lg:px-8 lg:py-20">
            <div class="max-w-lg">
                <p class="text-sm uppercase tracking-widest text-gray-500">{{ __('Own the room') }}</p>
                <h1 class="mt-3 text-4xl font-semibold leading-tight sm:text-5xl">{{ __('Falta só um passo.') }}</h1>
                <p class="mt-5 text-base leading-relaxed text-gray-600">{{ __('A sua conta B-Su está criada. Confirme o seu email para continuar a descobrir a coleção e acompanhar as suas encomendas.') }}</p>
            </div>
            <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 sm:p-10 lg:justify-self-end">
                <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-full bg-stone-100 text-gray-900">
                    <svg aria-hidden="true" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <path d="m3 7 9 6 9-6" />
                    </svg>
                </div>
                <h2 class="text-2xl font-semibold">{{ __('Verifique o seu email') }}</h2>
                <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ __('Para confirmar a sua conta, abra a mensagem de verificação e clique no link enviado para:') }}</p>
                <p class="mt-3 break-words font-medium text-gray-900">{{ auth()->user()->email }}</p>
                @if (session('status') == 'verification-link-sent')
                    <div role="status" class="mt-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                        {{ __('Foi enviado um novo link de verificação. Consulte a sua caixa de entrada e a pasta de spam.') }}
                    </div>
                @endif
                <x-input-error :messages="$errors->all()" class="mt-4" />
                <form method="POST" action="{{ route('verification.send') }}" class="mt-8">
                    @csrf
                    <button type="submit" class="w-full rounded-full bg-gray-900 px-6 py-3 text-sm font-medium text-white transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">{{ __('Reenviar email de verificação') }}</button>
                </form>
                <p class="mt-4 text-center text-xs leading-relaxed text-gray-500">{{ __('Não encontra a mensagem? Verifique também a pasta de spam.') }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-8 border-t border-gray-100 pt-6 text-center">
                    @csrf
                    <button type="submit" class="rounded text-sm font-medium text-gray-600 underline underline-offset-4 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">{{ __('Terminar sessão') }}</button>
                </form>
            </div>
        </div>
    </section>
@endsection
