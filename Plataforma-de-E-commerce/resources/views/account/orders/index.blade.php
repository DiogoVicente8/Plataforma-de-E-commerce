@extends('layouts.shop')

@section('title', __('As minhas encomendas'))

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="mb-6 text-2xl font-semibold">{{ __('As minhas encomendas') }}</h1>

        @if ($orders->isEmpty())
            <p class="text-gray-600">{{ __('Ainda não fizeste nenhuma encomenda.') }}</p>
            <a href="{{ route('shop.index') }}" class="mt-4 inline-block underline">{{ __('Ir para a loja') }}</a>
        @else
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">{{ __('Encomenda') }}</th>
                            <th class="px-4 py-3">{{ __('Data') }}</th>
                            <th class="px-4 py-3">{{ __('Estado') }}</th>
                            <th class="px-4 py-3">{{ __('N.º de artigos') }}</th>
                            <th class="px-4 py-3">{{ __('Total') }}</th>
                            <th class="px-4 py-3"><span class="sr-only">{{ __('Detalhes') }}</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($orders as $order)
                            <tr>
                                <td class="px-4 py-3">{{ '#'.$order->id }}</td>
                                <td class="px-4 py-3">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3"><x-order-status-badge :status="$order->status" /></td>
                                <td class="px-4 py-3">{{ $order->items_count }}</td>
                                <td class="px-4 py-3">{{ $order->formattedTotal() }}</td>
                                <td class="px-4 py-3"><a href="{{ route('account.orders.show', $order) }}" class="underline">{{ __('Ver') }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-6">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
