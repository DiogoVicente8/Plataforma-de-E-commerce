@props(['product'])

@php($image = $product->images->first())

<article class="group relative">
    <div class="aspect-[3/4] overflow-hidden rounded-lg bg-gray-100">
        @if ($image)
            <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $image->alt_text ?? $product->name }}" loading="lazy"
                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
        @else
            <div class="flex h-full items-center justify-center text-sm text-gray-400">{{ __('Sem imagem') }}</div>
        @endif
    </div>
    <div class="mt-3">
        @if (isset($product->in_stock) && ! $product->in_stock)
            <span class="mb-2 inline-block rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">{{ __('Esgotado') }}</span>
        @endif
        <p class="text-xs uppercase tracking-wide text-gray-500">{{ $product->category->name }}</p>
        <h3 class="mt-1 text-sm font-medium">
            <a href="{{ route('shop.product', $product) }}" class="before:absolute before:inset-0">{{ $product->name }}</a>
        </h3>
        <x-price :cents="$product->price_cents" class="mt-1 block text-sm font-semibold" />
        <div class="relative z-10 mt-3"><x-favorite-button :product="$product" /></div>
    </div>
</article>
