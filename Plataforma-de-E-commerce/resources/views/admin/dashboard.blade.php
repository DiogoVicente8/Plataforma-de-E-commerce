<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Painel de Administração') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __('Bem-vindo, :name.', ['name' => auth()->user()->name]) }}
                    <div class="mt-4">
                        <a class="text-indigo-600 underline hover:text-indigo-900" href="{{ route('admin.orders.index') }}">{{ __('Gerir encomendas') }}</a>
                        <a class="ml-4 text-indigo-600 underline hover:text-indigo-900" href="{{ route('admin.categories.index') }}">{{ __('Gerir categorias') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
