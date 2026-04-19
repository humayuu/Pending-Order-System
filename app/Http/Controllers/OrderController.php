<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->withCount('items')
            ->latest()
            ->paginate(15);

        return view('orders.index', compact('orders'));
    }

    public function create(): View
    {
        return view('orders.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.po_number' => ['required', 'string', 'max:255'],
            'lines.*.notes' => ['nullable', 'string'],
            'lines.*.item_name' => ['required', 'string', 'max:255'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'lines.*.po_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:12288'],
        ]);

        DB::transaction(function () use ($validated, $request): void {
            $order = Order::query()->create([
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
        $order->load(['items' => function ($q) {
            $q->withSum('deliveryChallanLines as delivered_sum', 'quantity');
        }]);

        return view('orders.show', compact('order'));
    }
}
