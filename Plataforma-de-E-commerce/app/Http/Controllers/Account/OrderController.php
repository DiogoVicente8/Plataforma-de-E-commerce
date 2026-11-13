<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Order::class);

        $orders = $request->user()->orders()->latest()->withCount('items')->paginate(10);

        return view('account.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load('items.product.images');

        return view('account.orders.show', compact('order'));
    }
}
