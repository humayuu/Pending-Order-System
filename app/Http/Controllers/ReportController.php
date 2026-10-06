<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use App\Services\PendingStockService;
use App\Support\StockFilters;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(protected PendingStockService $stock) {}

    public function index(): RedirectResponse
    {
        return redirect()->route('reports.item-and-po-wise');
    }

    public function itemAndPoWise(Request $request): View
    {
        $filters = StockFilters::fromRequest($request);

        return view('reports.item-and-po-wise', ['blocks' => $this->stock->itemPoMatrix($filters)] + $this->filterData($filters));
    }

    public function itemAndPoWisePdf(Request $request): Response
    {
        $filters = StockFilters::fromRequest($request);

        return $this->pdfResponse(
            'reports.pdf.item-and-po-wise',
            ['blocks' => $this->stock->itemPoMatrix($filters)],
            'Order summary (Item × PO)',
            'item-and-po-wise-report',
            $filters,
        );
    }

    protected function filterData(StockFilters $filters): array
    {
        return [
            'filters' => $filters,
            'clients' => Client::query()->orderBy('name')->get(['id', 'name']),
            'orders' => Order::query()->forClient($filters->clientId)->latest('id')->limit(200)->pluck('id'),
        ];
    }

    protected function pdfResponse(string $view, array $data, string $title, string $filePrefix, StockFilters $filters): Response
    {
        $clientName = $filters->clientId ? Client::query()->whereKey($filters->clientId)->value('name') : null;

        $pdf = Pdf::loadView($view, $data + [
            'pdfTitle' => $title,
            'generatedAt' => now()->format('Y-m-d H:i'),
            'filterSummary' => $filters->describe($clientName),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filePrefix.'-'.now()->format('Y-m-d').'.pdf');
    }
}
