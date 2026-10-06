<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DeliveryChallan;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\StockFilters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
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

        return view('orders.create', compact('clients', 'selectedClient'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.po_number' => ['required', 'string', 'max:255'],
            'lines.*.notes' => ['nullable', 'string'],
            'lines.*.item_name' => ['required', 'string', 'max:255'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'lines.*.po_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:12288'],
        ]);

        DB::transaction(function () use ($validated, $request): void {
            $order = Order::query()->create([
                'client_id' => $validated['client_id'],
                'reference' => null,
                'notes' => null,
            ]);

            foreach ($validated['lines'] as $index => $line) {
                $path = null;
                if ($request->hasFile("lines.$index.po_pdf")) {
                    $path = $request->file("lines.$index.po_pdf")->store('po_pdfs', 'public');
                }

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'item_name' => $line['item_name'],
                    'po_number' => $line['po_number'],
                    'notes' => $line['notes'] ?? null,
                    'quantity' => $line['quantity'],
                    'po_pdf_path' => $path,
                ]);
            }
        });

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

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        if ((int) $validated['client_id'] !== (int) $order->client_id) {
            $deliveredToOthers = DeliveryChallan::query()
                ->where('client_id', '!=', $validated['client_id'])
                ->whereHas('lines.orderItem', fn ($q) => $q->where('order_id', $order->id))
                ->exists();

            if ($deliveredToOthers) {
                return back()->withInput()->withErrors([
                    'client_id' => 'Cannot change the client: challans for another client already deliver against this order.',
                ]);
            }
        }

        $order->update($validated);

        return redirect()->route('orders.show', $order)->with('status', 'Order updated.');
    }
}
