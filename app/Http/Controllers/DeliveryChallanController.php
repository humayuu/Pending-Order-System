<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DeliveryChallan;
use App\Models\DeliveryChallanLine;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DeliveryChallanController extends Controller
{
    public function index(): View
    {
        $challans = DeliveryChallan::query()
            ->with('client')
            ->withSum('lines as total_qty', 'quantity')
            ->latest()
            ->paginate(15);

        return view('challans.index', compact('challans'));
    }

    public function create(): View
    {
        $clients = Client::query()->orderBy('name')->get();

        $availableLines = OrderItem::query()
            ->with(['order'])
            ->withSum('deliveryChallanLines as delivered_sum', 'quantity')
            ->orderBy('item_name')
            ->get()
            ->map(function (OrderItem $item) {
                $delivered = (int) ($item->delivered_sum ?? 0);
                $pending = max(0, $item->quantity - $delivered);

                return [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                    'po_number' => $item->po_number,
                    'pending' => $pending,
                    'order_id' => $item->order_id,
                    'line_notes' => $item->notes ? Str::limit($item->notes, 50) : null,
                ];
            })
            ->filter(fn (array $row) => $row['pending'] > 0)
            ->values();

        return view('challans.create', compact('clients', 'availableLines'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'issued_on' => ['required', 'date'],
            'vehicle_no' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.order_item_id' => ['required', 'exists:order_items,id'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $ids = Collection::make($validated['lines'])->pluck('order_item_id');
        if ($ids->count() !== $ids->unique()->count()) {
            return back()->withInput()->withErrors([
                'lines' => 'Each PO line can only appear once on a challan.',
            ]);
        }

        try {
            DB::transaction(function () use ($validated, $ids): void {
                $sortedIds = $ids->unique()->sort()->values()->all();

                $items = OrderItem::query()
                    ->whereIn('id', $sortedIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($items->count() !== count($sortedIds)) {
                    throw new \RuntimeException('One or more stock lines are invalid or no longer exist.');
                }

                foreach ($validated['lines'] as $line) {
                    $item = $items->get($line['order_item_id']);
                    if (! $item) {
                        throw new \RuntimeException('Invalid stock line selected.');
                    }

                    $delivered = (int) DeliveryChallanLine::query()
                        ->where('order_item_id', $item->id)
                        ->sum('quantity');

                    $pending = max(0, $item->quantity - $delivered);

                    if ($line['quantity'] > $pending) {
                        throw new \RuntimeException(
                            "Quantity exceeds pending for {$item->item_name} (PO {$item->po_number}). You entered {$line['quantity']}, pending is {$pending}."
                        );
                    }
                }

                $challan = DeliveryChallan::query()->create([
                    'client_id' => $validated['client_id'],
                    'challan_number' => $this->nextChallanNumber(),
                    'issued_on' => $validated['issued_on'],
                    'vehicle_no' => $validated['vehicle_no'] ?? null,
                    'remarks' => $validated['remarks'] ?? null,
                ]);

                foreach ($validated['lines'] as $line) {
                    DeliveryChallanLine::query()->create([
                        'delivery_challan_id' => $challan->id,
                        'order_item_id' => $line['order_item_id'],
                        'quantity' => $line['quantity'],
                    ]);
                }
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['lines' => $e->getMessage()]);
        }

        return redirect()->route('challans.index')->with('status', 'Delivery challan created.');
    }

    public function show(DeliveryChallan $challan): View
    {
        $challan->load(['client', 'lines.orderItem.order']);

        return view('challans.show', compact('challan'));
    }

    protected function nextChallanNumber(): string
    {
        $prefix = 'DC-'.now()->format('Y').'-';
        $last = DeliveryChallan::query()
            ->where('challan_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('challan_number');

        $seq = 1;
        if ($last && Str::startsWith($last, $prefix)) {
            $seq = (int) Str::after($last, $prefix) + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
