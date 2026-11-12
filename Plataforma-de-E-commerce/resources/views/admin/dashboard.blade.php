<x-app-layout>
    <x-slot name="header">
        <h1 class="text-3xl font-semibold leading-tight sm:text-4xl">{{ __('Painel de Administração') }}</h1>
        <p class="mt-3 text-gray-600">{{ __('Bem-vindo, :name.', ['name' => auth()->user()->name]) }} {{ __('Acompanhe e organize a sua loja num só lugar.') }}</p>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-6 md:grid-cols-3">
            @foreach ([['01', 'Encomendas', 'Consulte as encomendas e acompanhe o seu estado.', 'admin.orders.index', 'Gerir encomendas'], ['02', 'Produtos', 'Organize as peças, os tamanhos e o stock da coleção.', 'admin.products.index', 'Gerir produtos'], ['03', 'Categorias', 'Mantenha a coleção organizada e fácil de explorar.', 'admin.categories.index', 'Gerir categorias']] as [$number, $title, $description, $route, $action])
                <article class="flex flex-col rounded-2xl border border-gray-200 bg-white p-6 sm:p-8">
                    <span aria-hidden="true" class="text-sm tracking-widest text-gray-400">{{ $number }}</span>
                    <h2 class="mt-6 text-2xl font-semibold">{{ __($title) }}</h2>
                    <p class="mb-8 mt-3 flex-1 text-sm leading-relaxed text-gray-600">{{ __($description) }}</p>
                    <a href="{{ route($route) }}" class="inline-flex items-center justify-center rounded-full bg-gray-900 px-6 py-3 text-sm font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">{{ __($action) }} <span aria-hidden="true" class="ml-2">&rarr;</span></a>
                </article>
            @endforeach
        </div>
    </div>
</x-app-layout>
