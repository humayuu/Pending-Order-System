<?php

namespace App\Services;

use App\Models\DeliveryChallan;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create an order with its PO lines (and optional PDFs) atomically.
     *
     * @param  array{client_id: int|string, po_number: string, notes?: string|null, lines: array<int, array<string, mixed>>}  $data
     */
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $order = Order::query()->create([
                'client_id' => $data['client_id'],
                'reference' => null,
                'notes' => null,
            ]);

            foreach ($data['lines'] as $line) {
                $file = $line['file'] ?? null;
                $isPdf = $file?->getMimeType() === 'application/pdf';

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'item_name' => $line['item_name'],
                    'po_number' => $data['po_number'],
                    'notes' => $data['notes'] ?? null,
                    'quantity' => $line['quantity'],
                    'po_pdf_path' => $isPdf ? $file->store('po_pdfs', 'public') : null,
                    'image_path' => $file && ! $isPdf ? $file->store('po_images', 'public') : null,
                ]);
            }

            return $order;
        });
    }

    /** True when a challan for a client other than $clientId already delivers against this order. */
    public function hasDeliveriesForOtherClient(Order $order, int $clientId): bool
    {
        return DeliveryChallan::query()
            ->where('client_id', '!=', $clientId)
            ->whereHas('lines.orderItem', fn ($q) => $q->where('order_id', $order->id))
            ->exists();
    }
}
