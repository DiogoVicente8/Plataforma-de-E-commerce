@extends('layouts.shop')

@section('title', __('Favoritos'))

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-semibold">{{ __('Os meus favoritos') }}</h1>
        <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-3 lg:grid-cols-4">
            @forelse ($products as $product)
                <x-product-card :product="$product" />
            @empty
                <div class="col-span-full rounded-xl bg-stone-100 p-8">
                    <p class="text-gray-600">{{ __('Ainda não guardou peças nos favoritos.') }}</p>
                    <a href="{{ route('shop.index') }}" class="mt-4 inline-block rounded-full bg-gray-900 px-6 py-3 text-sm text-white hover:bg-gray-700">{{ __('Explorar a coleção') }}</a>
                </div>
            @endforelse
        </div>
        <div class="mt-8">{{ $products->links() }}</div>
    </section>
@endsection
