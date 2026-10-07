<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use App\Support\StockFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orders) {}

    public function index(Request $request): View
    {
        $filters = StockFilters::fromRequest($request);

        $orders = Order::query()
            ->with('client')
            ->withCount('items')
            ->when($filters->unassigned, fn ($q) => $q->unassigned())
            ->when($filters->clientId, fn ($q) => $q->forClient($filters->clientId))
            ->when($filters->from, fn ($q) => $q->where('created_at', '>=', $filters->from->copy()->startOfDay()))
            ->when($filters->to, fn ($q) => $q->where('created_at', '<=', $filters->to->copy()->endOfDay()))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $clients = Client::query()->orderBy('name')->get(['id', 'name']);

        return view('orders.index', compact('orders', 'clients', 'filters'));
    }

    public function create(Request $request): View
    {
        $clients = Client::query()->orderBy('name')->get(['id', 'name']);
        $selectedClient = $request->query('client_id');

        $itemNames = OrderItem::query()->select('item_name')->distinct()->orderBy('item_name')->pluck('item_name');

        return view('orders.create', compact('clients', 'selectedClient', 'itemNames'));
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $this->orders->create($request->validated());

        return redirect()->route('orders.index')->with('status', 'Order and PO lines saved.');
    }

    public function show(Order $order): View
    {
        $order->load(['client', 'items' => function ($q) {
            $q->withSum('deliveryChallanLines as delivered_sum', 'quantity');
        }]);

        return view('orders.show', compact('order'));
    }

    public function edit(Order $order): View
    {
        $clients = Client::query()->orderBy('name')->get(['id', 'name']);

        return view('orders.edit', compact('order', 'clients'));
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $order->update($request->validated());

        return redirect()->route('orders.show', $order)->with('status', 'Order updated.');
    }
}
