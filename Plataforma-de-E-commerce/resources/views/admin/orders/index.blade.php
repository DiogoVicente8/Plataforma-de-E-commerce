<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Encomendas') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4 text-green-800" role="status">{{ __(session('success')) }}</div>
            @endif

            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <x-input-label for="status" :value="__('Estado')" />
                        <select id="status" name="status" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('Todos') }}</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected($currentStatus === $status)>{{ __($status->label()) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button>{{ __('Filtrar') }}</x-primary-button>
                    @if ($currentStatus)
                        <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-600 underline hover:text-gray-900">{{ __('Limpar filtro') }}</a>
                    @endif
                </form>
            </div>

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach (['#', 'Cliente', 'Data', 'Artigos', 'Estado', 'Total', ''] as $heading)
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __($heading) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($orders as $order)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $order->id }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $order->user?->name ?? __('Utilizador removido') }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $order->items_count }}</td>
                                    <td class="px-6 py-4"><x-order-status-badge :status="$order->status" /></td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $order->formattedTotal() }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm"><a class="text-indigo-600 hover:text-indigo-900" href="{{ route('admin.orders.show', $order) }}">{{ __('Ver') }}</a></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">{{ __('Não existem encomendas.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-6">{{ $orders->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
