@props(['product'])

@if (! auth()->user()?->isAdmin())
@auth
    @php($isFavorite = auth()->user()->loadMissing('favorites')->favorites->contains('id', $product->id))
    <form method="POST" action="{{ route($isFavorite ? 'favorites.destroy' : 'favorites.store', $product) }}">
        @csrf
        @if ($isFavorite)
            @method('DELETE')
        @endif
        <button type="submit" aria-pressed="{{ $isFavorite ? 'true' : 'false' }}" class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-2 text-sm hover:border-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
            <span aria-hidden="true">{{ $isFavorite ? '♥' : '♡' }}</span>
            {{ $isFavorite ? __('Remover dos favoritos') : __('Guardar nos favoritos') }}
        </button>
    </form>
@else
    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-2 text-sm hover:border-gray-900">
        <span aria-hidden="true">♡</span> {{ __('Entrar para guardar nos favoritos') }}
    </a>
@endauth

@endif

