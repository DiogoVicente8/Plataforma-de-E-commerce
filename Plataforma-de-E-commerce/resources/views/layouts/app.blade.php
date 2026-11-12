<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'B-Su') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="{{ request()->routeIs('admin.*') ? 'flex min-h-screen flex-col bg-white text-gray-900' : 'min-h-screen bg-gray-100' }}">
            @if (request()->routeIs('admin.*'))
                @include('layouts.admin-navigation')
            @else
            @include('layouts.navigation')
            @endif

            <!-- Page Heading -->
            @isset($header)
                <header class="{{ request()->routeIs('admin.*') ? 'border-b border-gray-200 bg-stone-100' : 'bg-white shadow' }}">
                    <div class="max-w-7xl mx-auto {{ request()->routeIs('admin.*') ? 'py-10 sm:py-12' : 'py-6' }} px-4 sm:px-6 lg:px-8">
                        @if (request()->routeIs('admin.*'))
                            <p class="mb-3 text-xs uppercase tracking-widest text-gray-500">{{ __('Administração') }}</p>
                        @endif
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="{{ request()->routeIs('admin.*') ? 'flex-1' : '' }}">
                {{ $slot }}
            </main>
            @if (request()->routeIs('admin.*'))
                <footer class="border-t border-gray-200 bg-gray-50">
                    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-6 text-sm sm:px-6 lg:px-8">
                        <p class="font-semibold">{{ config('app.name') }}</p>
                        <p class="text-gray-500">{{ __('Roupa de autor, feita para durar.') }}</p>
                        <p class="text-xs text-gray-500">© {{ date('Y') }} {{ config('app.name') }}</p>
                    </div>
                </footer>
            @endif
        </div>
    </body>
</html>
