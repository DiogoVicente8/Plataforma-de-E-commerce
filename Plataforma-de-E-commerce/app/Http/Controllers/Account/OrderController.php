<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\StripeCheckout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Stripe\Exception\ApiErrorException;

class OrderController extends Controller
{
    public function cancel(Request $request, Order $order, StripeCheckout $stripe): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        try {
            $stripe->cancel($order);
        } catch (ApiErrorException $exception) {
            report($exception);

            return back()->withErrors(['order' => __('Não foi possível anular a encomenda no Stripe. Tenta novamente.')]);
        }

        return redirect()->route('account.orders.show', $order)->with('status', __('Encomenda anulada.'));
    }

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Order::class);

        $orders = $request->user()->orders()->latest()->withCount('items')->paginate(10);

        return view('account.orders.index', compact('orders'));
    }

    public function show(Order $order, StripeCheckout $stripe): View
    {
        Gate::authorize('view', $order);

        try {
            $stripe->confirmPayment($order);
        } catch (ApiErrorException $exception) {
            report($exception);
        }

        $order->load('items.product.images');

        return view('account.orders.show', compact('order'));
    }
}
