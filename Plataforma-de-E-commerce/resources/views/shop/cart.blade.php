@extends('layouts.shop')

@section('title', __('Carrinho'))

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-semibold">{{ __('O meu carrinho') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ __('O stock e os preços são atualizados ao abrir o carrinho. Os artigos ficam reservados apenas após a compra.') }}</p>
        <x-input-error :messages="$errors->all()" class="mt-4" />
        <div class="mt-8 space-y-4">
            @forelse ($items as $item)
                @php($variant = $item['variant'])
                <article class="flex flex-wrap items-center justify-between gap-6 rounded-xl border border-gray-200 p-6">
                    <div>
                        <a href="{{ route('shop.product', $variant->product) }}" class="font-medium hover:underline">{{ $variant->product->name }}</a>
                        <p class="mt-1 text-sm text-gray-600">{{ $variant->size }} · {{ $variant->color }}</p>
                        <x-price :cents="$item['price']" class="mt-2 block text-sm" />
                        @if ($item['quantity'] === 0)
                            <p class="mt-2 text-sm text-red-600">{{ __('Sem stock. Este artigo foi retirado do carrinho.') }}</p>
                        @else
                            <form method="POST" action="{{ route('cart.update', $variant) }}" class="mt-4 flex flex-wrap items-center gap-3">
                                @csrf
                                @method('PATCH')
                                <label for="quantity-{{ $variant->id }}" class="text-sm">{{ __('Quantidade') }}</label>
                                <input id="quantity-{{ $variant->id }}" name="quantity" type="number" min="1" max="{{ $variant->stock }}" value="{{ $item['quantity'] }}" required class="w-20 rounded-lg border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                                <button type="submit" class="rounded-full border border-gray-300 px-4 py-2 text-sm hover:border-gray-900">{{ __('Atualizar') }}</button>
                            </form>
                        @endif
                    </div>
                    <div class="flex items-center gap-6">
                        <x-price :cents="$item['subtotal']" class="font-semibold" />
                        <form method="POST" action="{{ route('cart.destroy', $variant) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" aria-label="{{ __('Remover') }} {{ $variant->product->name }}" class="text-sm text-gray-600 underline hover:text-gray-900">{{ __('Remover') }}</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="rounded-xl bg-stone-100 p-8 text-gray-600">{{ __('O seu carrinho está vazio.') }}</p>
            @endforelse
        </div>
        @if ($items->isNotEmpty())
            <div class="mt-8 flex items-center justify-end gap-4 text-xl font-semibold">
                <span>{{ __('Total dos artigos') }}</span>
                <x-price :cents="$total" />
            </div>
        @endif
        <a href="{{ route('shop.index') }}" class="mt-8 inline-block rounded-full bg-gray-900 px-6 py-3 text-sm text-white hover:bg-gray-700">{{ __('Continuar a comprar') }}</a>
    </section>
@endsection
