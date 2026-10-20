@props(['product'])

@php
    $image = $product->images->first();
    // O link para a página do produto aparece quando essa rota existir (semana 6).
    $href = Route::has('shop.product') ? route('shop.product', $product->slug) : null;
@endphp

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
        <p class="text-xs uppercase tracking-wide text-gray-500">{{ $product->category->name }}</p>
        <h3 class="mt-1 text-sm font-medium">
            @if ($href)
                <a href="{{ $href }}" class="before:absolute before:inset-0">{{ $product->name }}</a>
            @else
                {{ $product->name }}
            @endif
        </h3>
        <x-price :cents="$product->price_cents" class="mt-1 block text-sm font-semibold" />
    </div>
</article>