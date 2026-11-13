@extends('layouts.shop')

@section('title', __('O meu perfil'))

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <header class="mb-8">
            <h1 class="text-2xl font-semibold">{{ __('O meu perfil') }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ __('Gere os teus dados pessoais e a segurança da tua conta.') }}</p>
        </header>

        <div class="grid items-start gap-6 lg:grid-cols-2">
            <div class="rounded-xl border border-gray-200 p-6 sm:p-8">
                @include('profile.partials.update-profile-information-form')
            </div>
            <div class="rounded-xl border border-gray-200 p-6 sm:p-8">
                @include('profile.partials.update-password-form')
            </div>
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-6 sm:p-8 lg:col-span-2">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
@endsection
