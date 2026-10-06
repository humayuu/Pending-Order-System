<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DeliveryChallan;
use App\Models\Order;
use App\Services\PendingStockService;
use App\Support\StockFilters;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, PendingStockService $stock): View
    {
        $filters = StockFilters::fromRequest($request);

        $summary = $stock->clientSummary(new StockFilters(status: 'all'));
        $pendingLines = $stock->lines(new StockFilters(
            clientId: $filters->clientId,
            unassigned: $filters->unassigned,
            status: 'pending',
        ), applyStatus: true);

        return view('dashboard', [
            'clientsCount' => Client::query()->count(),
            'ordersCount' => Order::query()->count(),
            'challansCount' => DeliveryChallan::query()->count(),
            'unassignedOrders' => Order::query()->unassigned()->count(),
            'overall' => $stock->overall($summary),
            'clientSummary' => $summary,
            'pendingLines' => $pendingLines,
            'clients' => Client::query()->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ]);
    }
}
