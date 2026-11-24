<?php

namespace App\Http\Controllers\Account;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderReceiptController extends Controller
{
    public function __invoke(Request $request, Order $order): Response
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless(in_array($order->status, [OrderStatus::Paid, OrderStatus::Shipped, OrderStatus::Refunded], true), 403);

        $order->load(['items', 'user']);
        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $pdf = new Dompdf($options);
        $pdf->loadHtml(view('account.orders.receipt', compact('order'))->render(), 'UTF-8');
        $pdf->setPaper('A4');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="comprovativo-encomenda-'.$order->id.'.pdf"',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
