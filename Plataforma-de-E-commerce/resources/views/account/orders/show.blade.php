@extends('layouts.shop')

@section('title', __('Encomenda #:number', ['number' => $order->id]))

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-wrap items-center gap-4">
            <h1 class="text-2xl font-semibold">{{ __('Encomenda #:number', ['number' => $order->id]) }}</h1>
            <span class="text-sm text-gray-600">{{ $order->created_at->format('d/m/Y H:i') }}</span>
            <x-order-status-badge :status="$order->status" />
            @if ($order->user_id === auth()->id() && in_array($order->status, [\App\Enums\OrderStatus::Paid, \App\Enums\OrderStatus::Shipped, \App\Enums\OrderStatus::Refunded], true))
                <a href="{{ route('account.orders.receipt', $order) }}" class="inline-flex items-center gap-2 rounded-full border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 sm:ml-auto">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4 16v4a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-4" /></svg>
                    {{ __('Descarregar comprovativo (PDF)') }}
                </a>
            @endif
        </div>
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <x-input-error :messages="$errors->all()" class="m-4" />
            @if ($order->status === \App\Enums\OrderStatus::Pending)
                <div class="border-b border-gray-200 bg-gray-50 px-5 py-5 sm:px-6">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ __('A aguardar pagamento') }}</p>
                            <p class="mt-1 text-sm text-gray-600">{{ __('Podes continuar o pagamento enquanto a reserva estiver disponível.') }}</p>
                        </div>
                        <div class="flex shrink-0 flex-wrap items-center gap-3">
                            @if ($order->stripe_checkout_session_id)
                                <form method="POST" action="{{ route('checkout.pay', $order) }}">
                                    @csrf
                                    <button type="submit" class="rounded-full bg-gray-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-gray-700">{{ __('Pagar com Stripe') }}</button>
                                </form>
                            @endif
                            @if ($order->user_id === auth()->id())
                                <form method="POST" action="{{ route('account.orders.cancel', $order) }}">
                                    @csrf
                                    <button type="submit" class="rounded-full px-4 py-2.5 text-sm font-medium text-gray-500 transition hover:bg-red-50 hover:text-red-700">{{ __('Anular encomenda') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
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
                            <td class="px-4 py-3">
                                @php($image = $item->product?->images->first())
                                <div class="flex items-center gap-4">
                                    @if ($image)
                                        <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $image->alt_text ?? $item->product_name }}" loading="lazy"
                                             class="h-24 w-16 shrink-0 rounded-md bg-gray-100 object-cover">
                                    @else
                                        <div class="flex h-24 w-16 shrink-0 items-center justify-center rounded-md bg-gray-100 px-1 text-center text-xs text-gray-400">{{ __('Sem imagem') }}</div>
                                    @endif
                                    <span>{{ $item->product_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">{{ $item->quantity }}</td>
                            <td class="px-4 py-3">{{ $item->formattedUnitPrice() }}</td>
                            <td class="px-4 py-3">{{ $item->formattedSubtotal() }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-gray-200 font-semibold">
                    <tr class="text-sm font-normal text-gray-600">
                        <th colspan="3" class="px-4 py-3">{{ __('Subtotal dos artigos') }}</th>
                        <td class="px-4 py-3"><x-price :cents="$order->total_cents - $order->shipping_cents" /></td>
                    </tr>
                    <tr class="text-sm font-normal text-gray-600">
                        <th colspan="3" class="px-4 py-3">{{ __('Portes de envio') }}</th>
                        <td class="px-4 py-3"><x-price :cents="$order->shipping_cents" /></td>
                    </tr>
                    <tr>
                        <th colspan="3" class="px-4 py-3">{{ __('Total') }}</th>
                        <td class="px-4 py-3">{{ $order->formattedTotal() }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @if ($order->shipping_address)
            <h2 class="mt-8 font-semibold">{{ __('Morada de entrega') }}</h2>
            <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $order->shipping_address }}</p>
        @endif
        <a href="{{ route('account.orders.index') }}" class="mt-6 inline-block underline">{{ __('Voltar às minhas encomendas') }}</a>
    </div>
@endsection
