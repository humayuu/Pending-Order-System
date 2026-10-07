<?php

namespace App\Services;

use App\Models\DeliveryChallan;
use App\Models\DeliveryChallanLine;
use App\Models\OrderItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class DeliveryChallanService
{
    /**
     * PO lines that still have pending quantity and belong to a client (for the challan form).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function availableLines(): Collection
    {
        return OrderItem::query()
            ->with('order')
            ->whereHas('order', fn ($q) => $q->whereNotNull('client_id'))
            ->withSum('deliveryChallanLines as delivered_sum', 'quantity')
            ->orderBy('item_name')
            ->get()
            ->map(fn (OrderItem $item) => [
                'id' => $item->id,
                'item_name' => $item->item_name,
                'po_number' => $item->po_number,
                'pending' => max(0, $item->quantity - (int) ($item->delivered_sum ?? 0)),
                'order_id' => $item->order_id,
                'client_id' => $item->order->client_id,
                'line_notes' => $item->notes ? Str::limit($item->notes, 50) : null,
            ])
            ->filter(fn (array $row) => $row['pending'] > 0)
            ->values();
    }

    /**
     * Create a challan, locking the selected PO lines and re-checking pending quantities.
     *
     * @param  array<string, mixed>  $data  validated challan data
     *
     * @throws RuntimeException on invalid lines, client mismatch or over-delivery
     */
    public function create(array $data): DeliveryChallan
    {
        return DB::transaction(function () use ($data): DeliveryChallan {
            $sortedIds = collect($data['lines'])->pluck('order_item_id')->unique()->sort()->values()->all();

            $items = OrderItem::query()
                ->with('order')
                ->whereIn('id', $sortedIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($items->count() !== count($sortedIds)) {
                throw new RuntimeException('One or more stock lines are invalid or no longer exist.');
            }

            foreach ($data['lines'] as $line) {
                $this->assertDeliverable($items->get($line['order_item_id']), (int) $data['client_id'], (int) $line['quantity']);
            }

            $challan = DeliveryChallan::query()->create([
                'client_id' => $data['client_id'],
                'challan_number' => $this->nextChallanNumber(),
                'issued_on' => $data['issued_on'],
                'vehicle_no' => $data['vehicle_no'] ?? null,
                'remarks' => $data['remarks'] ?? null,
            ]);

            foreach ($data['lines'] as $line) {
                DeliveryChallanLine::query()->create([
                    'delivery_challan_id' => $challan->id,
                    'order_item_id' => $line['order_item_id'],
                    'quantity' => $line['quantity'],
                ]);
            }

            return $challan;
        });
    }

    private function assertDeliverable(?OrderItem $item, int $clientId, int $quantity): void
    {
        if (! $item) {
            throw new RuntimeException('Invalid stock line selected.');
        }

        if ((int) $item->order->client_id !== $clientId) {
            throw new RuntimeException("PO {$item->po_number} ({$item->item_name}) does not belong to the selected client.");
        }

        $delivered = (int) DeliveryChallanLine::query()->where('order_item_id', $item->id)->sum('quantity');
        $pending = max(0, $item->quantity - $delivered);

        if ($quantity > $pending) {
            throw new RuntimeException(
                "Quantity exceeds pending for {$item->item_name} (PO {$item->po_number}). You entered {$quantity}, pending is {$pending}."
            );
        }
    }

    private function nextChallanNumber(): string
    {
        $prefix = 'DC-'.now()->format('Y').'-';
        $last = DeliveryChallan::query()
            ->where('challan_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('challan_number');

        $seq = $last && Str::startsWith($last, $prefix) ? (int) Str::after($last, $prefix) + 1 : 1;

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
