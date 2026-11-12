@php($isEdit = $address->exists)

@extends('layouts.shop')

@section('title', $isEdit ? __('Editar morada') : __('Nova morada'))

@section('content')
    <div class="mx-auto max-w-xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="mb-6 text-2xl font-semibold">{{ $isEdit ? __('Editar morada') : __('Nova morada') }}</h1>

        <form method="POST" action="{{ $isEdit ? route('account.addresses.update', $address) : route('account.addresses.store') }}" class="space-y-5">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div>
                <x-input-label for="label" :value="__('Nome da morada (opcional)')" />
                <x-text-input id="label" name="label" type="text" class="mt-1 block w-full" :value="old('label', $address->label)" placeholder="{{ __('Casa, Trabalho…') }}" />
                <x-input-error :messages="$errors->get('label')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="recipient_name" :value="__('Nome de quem recebe')" />
                <x-text-input id="recipient_name" name="recipient_name" type="text" class="mt-1 block w-full" :value="old('recipient_name', $address->recipient_name)" required autocomplete="name" />
                <x-input-error :messages="$errors->get('recipient_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="phone" :value="__('Telefone')" />
                <div class="mt-1 flex gap-2">
                    <select name="phone_country" aria-label="{{ __('Indicativo') }}" class="w-44 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (config('countries') as $code => $country)
                            <option value="{{ $code }}" @selected(old('phone_country', $address->phone_country ?? 'PT') === $code)>{{ $country['name'] }} ({{ $country['dial'] }})</option>
                        @endforeach
                    </select>
                    <x-text-input id="phone" name="phone" type="tel" class="block w-full" :value="old('phone', $address->phone)" required autocomplete="tel-national" />
                </div>
                <x-input-error :messages="$errors->get('phone_country')" class="mt-2" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="street" :value="__('Morada')" />
                <x-text-input id="street" name="street" type="text" class="mt-1 block w-full" :value="old('street', $address->street)" required autocomplete="street-address" />
                <x-input-error :messages="$errors->get('street')" class="mt-2" />
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <x-input-label for="postal_code" :value="__('Código postal')" />
                    <x-text-input id="postal_code" name="postal_code" type="text" class="mt-1 block w-full" :value="old('postal_code', $address->postal_code)" required placeholder="0000-000" autocomplete="postal-code" />
                </div>
                <div class="col-span-2">
                    <x-input-label for="city" :value="__('Localidade')" />
                    <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $address->city)" required autocomplete="address-level2" />
                </div>
            </div>
            <x-input-error :messages="$errors->get('postal_code')" />
            <x-input-error :messages="$errors->get('city')" />

            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $address->is_default)) class="rounded border-gray-300">
                {{ __('Usar como morada principal') }}
            </label>

            <div class="flex items-center gap-4">
                <x-primary-button>{{ $isEdit ? __('Guardar alterações') : __('Guardar morada') }}</x-primary-button>
                <a href="{{ route('account.addresses.index') }}" class="text-sm text-gray-600 underline hover:text-gray-900">{{ __('Cancelar') }}</a>
            </div>
        </form>
    </div>
@endsection