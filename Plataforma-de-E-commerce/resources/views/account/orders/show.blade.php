@extends('layouts.shop')

@section('title', __('Encomenda #:number', ['number' => $order->id]))

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-wrap items-center gap-4">
            <h1 class="text-2xl font-semibold">{{ __('Encomenda #:number', ['number' => $order->id]) }}</h1>
            <span class="text-sm text-gray-600">{{ $order->created_at->format('d/m/Y H:i') }}</span>
            <x-order-status-badge :status="$order->status" />
        </div>
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3">{{ __('Artigo') }}</th>
                        <th class="px-4 py-3">{{ __('Quantidade') }}</th>
                        <th class="px-4 py-3">{{ __('Preço unitário') }}</th>
                        <th class="px-4 py-3">{{ __('Subtotal') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="px-4 py-3">{{ $item->product_name }}</td>
                            <td class="px-4 py-3">{{ $item->quantity }}</td>
                            <td class="px-4 py-3">{{ $item->formattedUnitPrice() }}</td>
                            <td class="px-4 py-3">{{ $item->formattedSubtotal() }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-gray-200 font-semibold">
                    <tr>
                        <th colspan="3" class="px-4 py-3">{{ __('Total') }}</th>
                        <td class="px-4 py-3">{{ $order->formattedTotal() }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <a href="{{ route('account.orders.index') }}" class="mt-6 inline-block underline">{{ __('Voltar às minhas encomendas') }}</a>
    </div>
@endsection
