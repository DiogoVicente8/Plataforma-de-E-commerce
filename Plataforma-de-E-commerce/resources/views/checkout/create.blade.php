@extends('layouts.shop')

@section('title', __('Finalizar encomenda'))

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-semibold">{{ __('Finalizar encomenda') }}</h1>
        <x-input-error :messages="$errors->all()" class="mt-4" />

        <form method="POST" action="{{ route('checkout.store') }}" class="mt-8 grid gap-10 lg:grid-cols-2">
            @csrf

            {{-- Morada de entrega --}}
            <fieldset>
                <legend class="text-lg font-semibold">{{ __('Morada de entrega') }}</legend>
                <div class="mt-4 space-y-3">
                    @forelse ($addresses as $address)
                        <label class="flex cursor-pointer gap-3 rounded-lg border border-gray-200 p-4 text-sm has-[:checked]:border-gray-900">
                            <input type="radio" name="address_id" value="{{ $address->id }}" @checked(old('address_id', $addresses->first()->id) == $address->id) class="mt-1">
                            <span class="whitespace-pre-line">{{ $address->toShippingText() }}</span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-600">{{ __('Ainda não tens moradas guardadas.') }}</p>
                    @endforelse
                </div>
                <a href="{{ route('account.addresses.create') }}" class="mt-4 inline-block text-sm underline">{{ __('Adicionar uma morada') }}</a>
            </fieldset>

            {{-- Resumo --}}
            <div>
                <h2 class="text-lg font-semibold">{{ __('Resumo') }}</h2>
                <ul class="mt-4 divide-y divide-gray-200 text-sm">
                    @foreach ($items as $item)
                        <li class="flex justify-between gap-4 py-3">
                            <span>{{ $item['quantity'] }} × {{ $item['variant']->product->name }} ({{ $item['variant']->size }} · {{ $item['variant']->color }})</span>
                            <x-price :cents="$item['subtotal']" />
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4 flex justify-between border-t border-gray-200 pt-4 text-lg font-semibold">
                    <span>{{ __('Total') }}</span>
                    <x-price :cents="$total" />
                </div>
                <p class="mt-2 text-xs text-gray-500">{{ __('O pagamento é confirmado pela loja depois de feita a encomenda.') }}</p>
                <button type="submit" @disabled($addresses->isEmpty()) class="mt-6 w-full rounded-full bg-gray-900 px-6 py-3 text-sm text-white hover:bg-gray-700 disabled:opacity-50">{{ __('Confirmar encomenda') }}</button>
            </div>
        </form>
    </div>
@endsection