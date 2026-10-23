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

        {{-- Filtros e ordenação --}}
        <form method="GET" action="{{ url()->current() }}" class="mt-6 flex flex-wrap items-end gap-3 text-sm">
            <input type="hidden" name="q" value="{{ $search }}">

            <select name="size" class="rounded-md border-gray-300 text-sm">
                <option value="">{{ __('Tamanho') }}</option>
                @foreach ($sizes as $size)
                    <option value="{{ $size }}" @selected(($filters['size'] ?? '') === $size)>{{ $size }}</option>
                @endforeach
            </select>

            <select name="color" class="rounded-md border-gray-300 text-sm">
                <option value="">{{ __('Cor') }}</option>
                @foreach ($colors as $color)
                    <option value="{{ $color }}" @selected(($filters['color'] ?? '') === $color)>{{ $color }}</option>
                @endforeach
            </select>

            <input name="min_price" type="number" min="0" placeholder="{{ __('Mín. €') }}" value="{{ $filters['min_price'] ?? '' }}" class="w-24 rounded-md border-gray-300 text-sm">
            <input name="max_price" type="number" min="0" placeholder="{{ __('Máx. €') }}" value="{{ $filters['max_price'] ?? '' }}" class="w-24 rounded-md border-gray-300 text-sm">

            <label class="inline-flex items-center gap-2">
                <input type="checkbox" name="in_stock" value="1" @checked(! empty($filters['in_stock'])) class="rounded border-gray-300">
                {{ __('Só com stock') }}
            </label>

            <select name="sort" class="rounded-md border-gray-300 text-sm">
                <option value="name">{{ __('Nome') }}</option>
                <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>{{ __('Preço: mais baixo') }}</option>
                <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>{{ __('Preço: mais alto') }}</option>
                <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>{{ __('Novidades') }}</option>
            </select>

            <button class="rounded-full bg-gray-900 px-5 py-2 text-white hover:bg-gray-700">{{ __('Filtrar') }}</button>
            <a href="{{ url()->current() }}" class="underline">{{ __('Limpar') }}</a>
        </form>

        @if ($errors->any())
            <p class="mt-3 text-sm text-red-700">{{ $errors->first() }}</p>
        @endif

        <p class="mt-6 text-sm text-gray-500">{{ trans_choice(':count peça|:count peças', $products->total()) }}</p>

        <div class="mt-4 grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-3 lg:grid-cols-4">
            @forelse ($products as $product)
                <x-product-card :product="$product" />
            @empty
                <p class="col-span-full text-gray-500">{{ __('Não encontrámos nenhuma peça com estes filtros.') }}</p>
            @endforelse
        </div>

        <div class="mt-10">{{ $products->links() }}</div>
    </div>
@endsection