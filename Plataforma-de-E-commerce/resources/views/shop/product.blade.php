@extends('layouts.shop')

@section('title', $product->name)

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-500">
            <a href="{{ route('shop.index') }}" class="hover:underline">{{ __('Loja') }}</a> /
            <a href="{{ route('shop.category', $product->category) }}" class="hover:underline">{{ $product->category->name }}</a>
        </nav>

        <div class="mt-6 grid gap-10 lg:grid-cols-2">
            {{-- Galeria --}}
                <div class="mx-auto w-full max-w-md space-y-4">
                @forelse ($product->images as $image)
                    <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $image->alt_text ?? $product->name }}"
                         class="w-full rounded-lg bg-gray-100 object-cover">
                @empty
                    <div class="flex aspect-[3/4] items-center justify-center rounded-lg bg-gray-100 text-gray-400">{{ __('Sem imagem') }}</div>
                @endforelse
            </div>

            {{-- Informação --}}
            <div>
                <h1 class="text-3xl font-semibold">{{ $product->name }}</h1>
                <x-price :cents="$product->price_cents" class="mt-3 block text-xl" />

                @if ($product->description)
                    <p class="mt-6 whitespace-pre-line text-gray-700">{{ $product->description }}</p>
                @endif

                <fieldset class="mt-8">
                    <legend class="text-sm font-medium">{{ __('Tamanho e cor') }}</legend>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse ($variants as $variant)
                            <label @class([
                                'cursor-pointer rounded-md border px-4 py-2 text-sm has-[:checked]:border-gray-900 has-[:checked]:bg-gray-900 has-[:checked]:text-white',
                                'cursor-not-allowed text-gray-400 line-through' => $variant->stock === 0,
                            ])>
                                <input type="radio" name="variant_id" value="{{ $variant->id }}" class="sr-only" @disabled($variant->stock === 0)>
                                {{ $variant->size }} · {{ $variant->color }}
                            </label>
                        @empty
                            <p class="text-sm text-gray-500">{{ __('Esta peça ainda não tem tamanhos disponíveis.') }}</p>
                        @endforelse
                    </div>
                </fieldset>
            </div>
        </div>

        @if ($related->isNotEmpty())
            <section class="mt-20">
                <h2 class="text-2xl font-semibold">{{ __('Também vais gostar') }}</h2>
                <div class="mt-6 grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($related as $item)
                        <x-product-card :product="$item" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection