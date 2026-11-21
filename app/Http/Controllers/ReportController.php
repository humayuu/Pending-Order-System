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
use Illuminate\Support\Str;
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
            $filters,
        );
    }

    /**
     * Whole-order delivery report, one section per PO: item totals, then each DC that delivered against it.
     */
    public function poReportPdf(Order $order): Response
    {
        $items = $order->items()
            ->withSum('deliveryChallanLines as delivered_sum', 'quantity')
            ->with('deliveryChallanLines.deliveryChallan:id,challan_number,issued_on')
            ->orderBy('id')
            ->get();

        abort_if($items->isEmpty(), 404);

        $sections = $items->groupBy('po_number')->map(fn ($poItems, $po) => (object) [
            'po_number' => $po,
            'items' => $poItems,
            'deliveries' => $poItems
                ->flatMap(fn ($item) => $item->deliveryChallanLines->map(fn ($line) => (object) [
                    'issued_on' => $line->deliveryChallan->issued_on,
                    'challan_id' => $line->deliveryChallan->id,
                    'challan_number' => $line->deliveryChallan->challan_number,
                    'item_name' => $item->item_name,
                    'quantity' => $line->quantity,
                ]))
                ->sortBy([['issued_on', 'asc'], ['challan_id', 'asc']])
                ->values(),
        ])->values();

        $order->loadMissing('client');
        $poList = $sections->pluck('po_number')->implode(', ');

        $pdf = Pdf::loadView('reports.pdf.po-report', [
            'pdfTitle' => 'PO: '.$poList,
            'filterSummary' => $order->client ? 'Client: '.$order->client->name : null,
            'sections' => $sections,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('po-'.(Str::slug($poList) ?: 'report').'-'.now()->format('Y-m-d').'.pdf');
    }

    protected function filterData(StockFilters $filters): array
    {
        return [
            'filters' => $filters,
            'clients' => Client::query()->orderBy('name')->get(['id', 'name']),
            'orders' => Order::query()->forClient($filters->clientId)->latest('id')->limit(200)->pluck('id'),
        ];
    }

    /**
     * Downloads as "{client}-{status}.pdf", e.g. "Paul Brooks-Pending.pdf" or "All Clients-Completed.pdf".
     */
    protected function pdfResponse(string $view, array $data, string $title, StockFilters $filters): Response
    {
        $clientName = $filters->clientId ? Client::query()->whereKey($filters->clientId)->value('name') : null;

        $pdf = Pdf::loadView($view, $data + [
            'pdfTitle' => $title,
            'generatedAt' => now()->format('Y-m-d H:i'),
            'filterSummary' => $filters->describe($clientName),
        ])->setPaper('a4', 'landscape');

        // Strip characters Windows doesn't allow in file names.
        $client = trim(preg_replace(['/[\\\\\/:*?"<>|]+/', '/\s+/'], ' ', $clientName ?? '')) ?: 'All Clients';

        return $pdf->download($client.'-'.ucfirst($filters->status).'.pdf');
    }
}
