<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DeliveryChallan;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $pendingLines = OrderItem::query()
            ->with(['order'])
            ->withSum('deliveryChallanLines as delivered_sum', 'quantity')
            ->get()
            ->map(function (OrderItem $item) {
                $delivered = (int) ($item->delivered_sum ?? 0);
                $pending = max(0, $item->quantity - $delivered);

                return [
                    'item' => $item,
                    'pending' => $pending,
                ];
            })
            ->filter(fn (array $row) => $row['pending'] > 0)
            ->values();

        return view('dashboard', [
            'clientsCount' => Client::query()->count(),
            'ordersCount' => Order::query()->count(),
            'challansCount' => DeliveryChallan::query()->count(),
            'pendingLines' => $pendingLines,
        ]);
    }
}
