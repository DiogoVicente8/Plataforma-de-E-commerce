<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Comprovativo de compra #{{ $order->id }}</title>
    <style>
        @page { margin: 42px; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 11px; color: #253044; }
        .brand { font-size: 25px; font-weight: bold; margin-bottom: 28px; }
        h1 { font-size: 20px; margin-bottom: 8px; }
        h2 { font-size: 12px; margin-top: 26px; }
        .muted { color: #64748b; }
        .address { line-height: 1.7; }
        table { width: 100%; border-collapse: collapse; margin-top: 28px; }
        thead { display: table-header-group; }
        th { background: #f1f5f9; text-align: left; }
        th, td { padding: 12px 8px; border-bottom: 1px solid #e2e8f0; }
        .number { text-align: right; white-space: nowrap; }
        tfoot th, tfoot td { font-size: 13px; font-weight: bold; }
        tr { page-break-inside: avoid; }
        .footer { margin-top: 30px; font-size: 9px; color: #64748b; }
    </style>
</head>
<body>
    <div class="brand">{{ config('app.name') }}</div>
    <h1>Comprovativo de compra</h1>
    <p class="muted">Encomenda #{{ $order->id }} · {{ $order->created_at->format('d/m/Y H:i') }}</p>
    <p>Estado: <strong>{{ $order->status->label() }}</strong></p>

    <h2>Cliente</h2>
    <p>{{ $order->user->name }}<br>{{ $order->user->email }}</p>
    @if ($order->shipping_address)
        <h2>Morada de entrega</h2>
        <p class="address">{!! nl2br(e($order->shipping_address)) !!}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Artigo</th>
                <th class="number">Quantidade</th>
                <th class="number">Preço unitário</th>
                <th class="number">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="number">{{ $item->quantity }}</td>
                    <td class="number">{{ $item->formattedUnitPrice() }}</td>
                    <td class="number">{{ $item->formattedSubtotal() }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">Subtotal dos artigos</th>
                <td class="number">{{ \App\Support\Money::format($order->total_cents - $order->shipping_cents) }}</td>
            </tr>
            <tr>
                <th colspan="3">Portes de envio</th>
                <td class="number">{{ \App\Support\Money::format($order->shipping_cents) }}</td>
            </tr>
            <tr>
                <th colspan="3">Total da compra</th>
                <td class="number">{{ $order->formattedTotal() }}</td>
            </tr>
        </tfoot>
    </table>
    <p class="footer">Comprovativo da encomenda #{{ $order->id }}. Este documento não substitui uma fatura fiscal.</p>
</body>
</html>
