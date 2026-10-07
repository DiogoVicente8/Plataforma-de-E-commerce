<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Encomenda #:id', ['id' => $order->id]) }}</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-indigo-600 underline hover:text-indigo-900">{{ __('Voltar às encomendas') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4 text-green-800" role="status">{{ __(session('success')) }}</div>
            @endif

            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="mb-4 text-lg font-medium text-gray-900">{{ __('Detalhes da encomenda') }}</h3>
                <dl class="grid gap-4 sm:grid-cols-3">
                    <div><dt class="text-sm text-gray-500">{{ __('Cliente') }}</dt><dd class="font-medium text-gray-900">{{ $order->user?->name ?? __('Utilizador removido') }}</dd><dd class="text-sm text-gray-600">{{ $order->user?->email ?? '—' }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Data') }}</dt><dd class="font-medium text-gray-900">{{ $order->created_at->format('d/m/Y H:i') }}</dd></div>
                    <div><dt class="text-sm text-gray-500">{{ __('Estado') }}</dt><dd class="mt-1"><x-order-status-badge :status="$order->status" /></dd></div>
                </dl>
            </section>

            <section class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <h3 class="p-6 text-lg font-medium text-gray-900">{{ __('Artigos') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50"><tr>
                            @foreach (['Produto', 'Quantidade', 'Preço unitário', 'Subtotal'] as $heading)
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __($heading) }}</th>
                            @endforeach
                        </tr></thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $item->product_name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $item->formattedUnitPrice() }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $item->formattedSubtotal() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50"><tr><th colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">{{ __('Total') }}</th><td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $order->formattedTotal() }}</td></tr></tfoot>
                    </table>
                </div>
            </section>

            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="mb-4 text-lg font-medium text-gray-900">{{ __('Alterar estado') }}</h3>
                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    @method('PATCH')
                    <div>
                        <x-input-label for="status" :value="__('Estado')" />
                        <select id="status" name="status" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $order->status->value) === $status->value)>{{ __($status->label()) }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                    <x-primary-button>{{ __('Guardar') }}</x-primary-button>
                </form>
            </section>

            <section class="rounded-lg border border-red-200 bg-white p-6 shadow-sm">
                <h3 class="mb-2 text-lg font-medium text-gray-900">{{ __('Eliminar encomenda') }}</h3>
                <p class="mb-4 text-sm text-gray-600">{{ __('Esta ação elimina a encomenda e os respetivos artigos.') }}</p>
                <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirm('{{ __('Tem a certeza de que pretende eliminar esta encomenda?') }}');">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>{{ __('Eliminar encomenda') }}</x-danger-button>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
