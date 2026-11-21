<?php

namespace App\Services;

use App\Models\DeliveryChallan;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create an order with its PO lines atomically. The optional PO file is
     * uploaded once and its path stored on every line, like the notes.
     *
     * @param  array{client_id: int|string, po_number: string, notes?: string|null, po_file?: UploadedFile|null, lines: array<int, array<string, mixed>>}  $data
     */
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $order = Order::query()->create([
                'client_id' => $data['client_id'],
                'reference' => null,
                'notes' => null,
            ]);

            $file = $data['po_file'] ?? null;
            $isPdf = $file?->getMimeType() === 'application/pdf';
            $pdfPath = $file && $isPdf ? $file->store('po_pdfs', 'public') : null;
            $imagePath = $file && ! $isPdf ? $file->store('po_images', 'public') : null;

            foreach ($data['lines'] as $line) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'item_name' => $line['item_name'],
                    'po_number' => $data['po_number'],
                    'notes' => $data['notes'] ?? null,
                    'quantity' => $line['quantity'],
                    'po_pdf_path' => $pdfPath,
                    'image_path' => $imagePath,
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
