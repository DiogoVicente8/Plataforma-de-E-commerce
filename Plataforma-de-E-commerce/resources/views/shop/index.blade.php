@extends('layouts.shop')

@section('title', $category?->name ?? __('Loja'))

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-semibold">{{ $category?->name ?? __('Toda a coleção') }}</h1>

        @if ($search !== '')
            <p class="mt-2 text-gray-600">
                {{ __('Resultados para «:q»', ['q' => $search]) }} ·
                <a href="{{ url()->current() }}" class="underline">{{ __('limpar pesquisa') }}</a>
            </p>
        @endif
        <p class="mt-1 text-sm text-gray-500">{{ trans_choice(':count peça|:count peças', $products->total()) }}</p>

        <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-3 lg:grid-cols-4">
            @forelse ($products as $product)
                <x-product-card :product="$product" />
            @empty
                <p class="col-span-full text-gray-500">{{ __('Não encontrámos nenhuma peça.') }}</p>
            @endforelse
        </div>

        <div class="mt-10">{{ $products->links() }}</div>
    </div>
@endsection