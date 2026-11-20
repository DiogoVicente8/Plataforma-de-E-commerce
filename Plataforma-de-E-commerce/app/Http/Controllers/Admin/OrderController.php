<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Notifications\OrderStatusChanged;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $currentStatus = is_string($status) ? OrderStatus::tryFrom($status) : null;

        $orders = Order::query()
            ->when($currentStatus, fn ($query) => $query->where('status', $currentStatus->value))
            ->with('user')
            ->withCount('items')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
            'currentStatus' => $currentStatus,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'items']);

        return view('admin.orders.show', [
            'order' => $order,
            'statuses' => OrderStatus::cases(),
        ]);
    }

    public function update(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $previousStatus = $order->status;
        $order->update($request->validated());

        if ($order->wasChanged('status')) {
            $order->user?->notify(new OrderStatusChanged($order, $previousStatus));
        }

        Log::channel('audit')->info('Estado da encomenda alterado.', [
            'order_id' => $order->id,
            'previous_status' => $previousStatus->value,
            'new_status' => $order->status->value,
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('admin.orders.show', $order)->with('success', __('Estado da encomenda atualizado.'));
    }

    public function refund(Order $order): RedirectResponse
    {
        if (! in_array($order->status, [OrderStatus::Paid, OrderStatus::Shipped], true)) {
            return redirect()->route('admin.orders.show', $order)
                ->withErrors(['refund' => __('Só é possível reembolsar encomendas pagas ou enviadas.')]);
        }

        $previousStatus = $order->status;
        $order->update(['status' => OrderStatus::Refunded]);
        $order->user?->notify(new OrderStatusChanged($order, $previousStatus));

        Log::channel('audit')->info('Encomenda reembolsada.', [
            'order_id' => $order->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.orders.show', $order)->with('success', __('Encomenda marcada como reembolsada.'));
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        Log::channel('audit')->info('Encomenda eliminada.', [
            'order_id' => $order->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('admin.orders.index')->with('success', __('Encomenda eliminada.'));
    }
}
