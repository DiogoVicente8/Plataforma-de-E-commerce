<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Categorias') }}</h2>
            <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">{{ __('Nova categoria') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4 text-green-800" role="status">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded-md bg-red-50 p-4 text-red-800" role="alert">{{ session('error') }}</div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach (['Nome', 'Slug', 'Produtos', ''] as $heading)
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __($heading) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($categories as $category)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $category->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $category->slug }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $category->products_count }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <a class="text-indigo-600 hover:text-indigo-900" href="{{ route('admin.categories.edit', $category) }}">{{ __('Editar') }}</a>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="ms-4 inline" onsubmit="return confirm('{{ __('Eliminar esta categoria?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Eliminar') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">{{ __('Não existem categorias.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-6">{{ $categories->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>