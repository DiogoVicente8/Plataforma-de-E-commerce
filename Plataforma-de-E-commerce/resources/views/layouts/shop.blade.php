<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') · @endif{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-white font-sans text-gray-900 antialiased">
    {{-- Barra de anúncio --}}
    <div class="bg-gray-900 px-4 py-2 text-center text-xs text-white sm:text-sm">
        {{ __('Own the room.') }}
    </div>

    <header x-data="{ open: false }" class="border-b border-gray-200">
        <div class="mx-auto flex max-w-7xl items-center gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <button type="button" class="sm:hidden" @click="open = ! open" aria-label="{{ __('Abrir menu') }}">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <a href="{{ route('home') }}" class="text-xl font-semibold tracking-wide">{{ config('app.name') }}</a>

            {{-- Pesquisa --}}
            <form method="GET" action="{{ route('shop.index') }}" class="ms-auto hidden w-full max-w-xs sm:block">
                <label for="q" class="sr-only">{{ __('Pesquisar') }}</label>
                <input id="q" name="q" type="search" value="{{ request('q') }}" placeholder="{{ __('Pesquisar peças…') }}"
                       class="w-full rounded-full border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900">
            </form>

            <nav class="ms-auto flex items-center gap-4 text-sm sm:ms-4">
                {{-- Os ícones aparecem sozinhos quando as rotas existirem --}}
                @if (Route::has('favorites.index'))
                    <a href="{{ route('favorites.index') }}" aria-label="{{ __('Favoritos') }}" class="hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                    </a>
                @endif
                @if (Route::has('cart.index'))
                    <a href="{{ route('cart.index') }}" aria-label="{{ __('Carrinho') }}" class="hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                    </a>
                @endif

                {{-- Menu de conta --}}
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-gray-600">
                                {{ __('A minha conta') }}
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">{{ __('Perfil') }}</x-dropdown-link>
                            @if (Auth::user()->isAdmin())
                                <x-dropdown-link :href="route('admin.dashboard')">{{ __('Administração') }}</x-dropdown-link>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Sair') }}</x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="hover:text-gray-600">{{ __('Entrar') }}</a>
                    <a href="{{ route('register') }}" class="hidden rounded-full bg-gray-900 px-4 py-2 text-white hover:bg-gray-700 sm:inline-block">{{ __('Registar') }}</a>
                @endauth
            </nav>
        </div>

        {{-- Menu de categorias (computador) --}}
        <nav class="hidden border-t border-gray-100 sm:block" aria-label="{{ __('Categorias') }}">
            <ul class="mx-auto flex max-w-7xl gap-6 overflow-x-auto px-4 py-3 text-sm sm:px-6 lg:px-8">
                <li><a href="{{ route('shop.index') }}" class="{{ request()->routeIs('shop.index') ? 'font-semibold' : 'text-gray-600 hover:text-gray-900' }}">{{ __('Tudo') }}</a></li>
                @foreach ($menuCategories as $menuCategory)
                    <li>
                        <a href="{{ route('shop.category', $menuCategory->slug) }}"
                           class="{{ request()->route('category')?->is($menuCategory) ? 'font-semibold' : 'text-gray-600 hover:text-gray-900' }}">{{ $menuCategory->name }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>

        {{-- Menu de telemóvel --}}
        <div x-show="open" x-cloak class="border-t border-gray-100 px-4 py-4 sm:hidden">
            <form method="GET" action="{{ route('shop.index') }}">
                <label for="q-mobile" class="sr-only">{{ __('Pesquisar') }}</label>
                <input id="q-mobile" name="q" type="search" value="{{ request('q') }}" placeholder="{{ __('Pesquisar peças…') }}"
                       class="w-full rounded-full border-gray-300 text-sm focus:border-gray-900 focus:ring-gray-900">
            </form>
            <ul class="mt-4 space-y-3 text-sm">
                <li><a href="{{ route('shop.index') }}">{{ __('Tudo') }}</a></li>
                @foreach ($menuCategories as $menuCategory)
                    <li><a href="{{ route('shop.category', $menuCategory->slug) }}">{{ $menuCategory->name }}</a></li>
                @endforeach
            </ul>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-gray-200 bg-gray-50">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 text-sm sm:grid-cols-3 sm:px-6 lg:px-8">
            <div>
                <p class="text-base font-semibold">{{ config('app.name') }}</p>
                <p class="mt-2 text-gray-600">{{ __('Roupa de autor, feita para durar.') }}</p>
            </div>
            <div>
                <p class="font-semibold">{{ __('Loja') }}</p>
                <ul class="mt-2 space-y-2 text-gray-600">
                    <li><a href="{{ route('shop.index') }}" class="hover:text-gray-900">{{ __('Toda a coleção') }}</a></li>
                    @foreach ($menuCategories->take(4) as $menuCategory)
                        <li><a href="{{ route('shop.category', $menuCategory->slug) }}" class="hover:text-gray-900">{{ $menuCategory->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <p class="font-semibold">{{ __('Ajuda') }}</p>
                <ul class="mt-2 space-y-2 text-gray-600">
                    {{-- As páginas de contactos e políticas chegam na semana 9 --}}
                    @if (Route::has('pages.contact'))
                        <li><a href="{{ route('pages.contact') }}" class="hover:text-gray-900">{{ __('Contactos') }}</a></li>
                    @endif
                    @auth
                        <li><a href="{{ route('profile.edit') }}" class="hover:text-gray-900">{{ __('A minha conta') }}</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="hover:text-gray-900">{{ __('Entrar') }}</a></li>
                    @endauth
                </ul>
            </div>
        </div>
        <p class="border-t border-gray-200 py-4 text-center text-xs text-gray-500">© {{ date('Y') }} {{ config('app.name') }}</p>
    </footer>
</body>
</html>