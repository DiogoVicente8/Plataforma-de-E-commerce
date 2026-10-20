@extends('layouts.shop')

@section('title', __('Início'))

@section('content')
    <section class="bg-stone-100">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <p class="text-sm uppercase tracking-widest text-gray-500">{{ __('Own the room') }}</p>
             <h1 class="mt-3 max-w-xl text-4xl font-semibold leading-tight sm:text-5xl">{{ __('Para a mulher real que quer sentir-se bem todos os dias, sem esforço.') }}</h1>
            <a href="{{ route('shop.index') }}" class="mt-8 inline-block rounded-full bg-gray-900 px-6 py-3 text-sm font-medium text-white hover:bg-gray-700">{{ __('Ver a coleção') }}</a>
        </div>
    </section>

    @if ($categories->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-semibold">{{ __('Categorias') }}</h2>
            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($categories as $category)
                    <a href="{{ route('shop.category', $category->slug) }}" class="rounded-lg border border-gray-200 p-5 hover:border-gray-900">
                        <span class="block font-medium">{{ $category->name }}</span>
                        <span class="mt-1 block text-sm text-gray-500">{{ trans_choice(':count peça|:count peças', $category->products_count) }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between">
            <h2 class="text-2xl font-semibold">{{ __('Novidades') }}</h2>
            <a href="{{ route('shop.index') }}" class="text-sm underline hover:text-gray-600">{{ __('Ver tudo') }}</a>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-3 lg:grid-cols-4">
            @forelse ($featured as $product)
                <x-product-card :product="$product" />
            @empty
                <p class="col-span-full text-gray-500">{{ __('Ainda não há produtos à venda.') }}</p>
            @endforelse
        </div>
    </section>
@endsection