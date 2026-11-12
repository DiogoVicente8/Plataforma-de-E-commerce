<header class="border-b border-gray-200 bg-white">
    <div class="bg-gray-900 px-4 py-2 text-center text-xs text-white sm:text-sm">{{ __('Own the room.') }}</div>
    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-5 sm:px-6 lg:px-8">
        <a href="{{ route('admin.dashboard') }}" class="text-xl font-semibold tracking-wide">{{ config('app.name') }}</a>
        <div class="flex flex-wrap items-center gap-4 text-sm">
            <span class="hidden text-gray-500 sm:inline">{{ auth()->user()->name }}</span>
            <a href="{{ route('home') }}" class="rounded-full border border-gray-300 px-4 py-2 hover:border-gray-900">{{ __('Ver loja') }}</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-full bg-gray-900 px-4 py-2 text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">{{ __('Sair') }}</button>
            </form>
        </div>
    </div>
    <nav aria-label="{{ __('Administração') }}" class="border-t border-gray-100">
        <div class="mx-auto flex max-w-7xl gap-6 overflow-x-auto px-4 py-3 text-sm sm:px-6 lg:px-8">
            @foreach ([['admin.dashboard', 'admin.dashboard', 'Painel'], ['admin.orders.index', 'admin.orders.*', 'Encomendas'], ['admin.products.index', 'admin.products.*', 'Produtos'], ['admin.categories.index', 'admin.categories.*', 'Categorias']] as [$route, $pattern, $label])
                <a href="{{ route($route) }}" @if (request()->routeIs($pattern)) aria-current="page" @endif class="whitespace-nowrap {{ request()->routeIs($pattern) ? 'font-semibold text-gray-900' : 'text-gray-600 hover:text-gray-900' }}">{{ __($label) }}</a>
            @endforeach
        </div>
    </nav>
</header>
