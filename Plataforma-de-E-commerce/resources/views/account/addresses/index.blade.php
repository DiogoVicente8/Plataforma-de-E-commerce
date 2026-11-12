@extends('layouts.shop')

@section('title', __('As minhas moradas'))

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold">{{ __('As minhas moradas') }}</h1>
            <a href="{{ route('account.addresses.create') }}" class="rounded-full bg-gray-900 px-5 py-2 text-sm text-white hover:bg-gray-700">{{ __('Nova morada') }}</a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($addresses as $address)
                <article class="rounded-lg border border-gray-200 p-5 text-sm">
                    <p class="font-medium">
                        {{ $address->label ?? __('Morada') }}
                        @if ($address->is_default)
                            <span class="ms-2 rounded-full bg-gray-900 px-2 py-0.5 text-xs text-white">{{ __('Principal') }}</span>
                        @endif
                    </p>
                    <p class="mt-2 text-gray-700">{{ $address->recipient_name }}</p>
                    <p class="text-gray-700">{{ $address->street }}</p>
                    <p class="text-gray-700">{{ $address->postal_code }} {{ $address->city }}</p>
                    <p class="text-gray-700">{{ $address->formattedPhone() }}</p>

                    <div class="mt-4 flex gap-4">
                        <a href="{{ route('account.addresses.edit', $address) }}" class="underline">{{ __('Editar') }}</a>
                        <form method="POST" action="{{ route('account.addresses.destroy', $address) }}" onsubmit="return confirm('{{ __('Eliminar esta morada?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-700 underline">{{ __('Eliminar') }}</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="text-gray-600">{{ __('Ainda não tens moradas guardadas.') }}</p>
            @endforelse
        </div>
    </div>
@endsection