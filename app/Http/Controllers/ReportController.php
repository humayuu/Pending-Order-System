<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index');
    }

    public function itemWise(): View
    {
        return view('reports.item-wise', ['rows' => $this->itemWiseRows()]);
    }

    public function itemWisePdf(): Response
    {
        return $this->pdfResponse(
            'reports.pdf.item-wise',
            [
                'rows' => $this->itemWiseRows(),
                'pdfTitle' => 'Item wise report',
                'generatedAt' => now()->format('Y-m-d H:i'),
            ],
            'item-wise-report-'.now()->format('Y-m-d').'.pdf',
            'landscape'
        );
    }

    public function itemAndPoWise(): View
    {
        return view('reports.item-and-po-wise', ['rows' => $this->itemAndPoWiseRows()]);
    }

    public function itemAndPoWisePdf(): Response
    {
        return $this->pdfResponse(
            'reports.pdf.item-and-po-wise',
            [
                'rows' => $this->itemAndPoWiseRows(),
                'pdfTitle' => 'Item and PO wise report',
                'generatedAt' => now()->format('Y-m-d H:i'),
            ],
            'item-and-po-wise-report-'.now()->format('Y-m-d').'.pdf',
            'landscape'
        );
    }

    protected function itemWiseRows(): Collection
    {
        $perLine = DB::table('order_items as oi')
            ->select('oi.item_name')
            ->selectRaw('oi.quantity as line_ordered')
            ->selectRaw('(SELECT COALESCE(SUM(dcl.quantity), 0) FROM delivery_challan_lines dcl WHERE dcl.order_item_id = oi.id) as line_delivered');

        return DB::query()->fromSub($perLine, 'x')
            ->select('item_name')
            ->selectRaw('SUM(line_ordered) as total_ordered')
            ->selectRaw('SUM(line_delivered) as total_delivered')
            ->groupBy('item_name')
            ->orderBy('item_name')
            ->get()
            ->map(function ($row) {
                $row->pending = max(0, (int) $row->total_ordered - (int) $row->total_delivered);

                return $row;
            })
            ->filter(fn ($row) => $row->pending > 0)
            ->values();
    }

    protected function itemAndPoWiseRows(): Collection
    {
        $perLine = DB::table('order_items as oi')
            ->select('oi.po_number', 'oi.item_name')
            ->selectRaw('oi.quantity as line_ordered')
            ->selectRaw('(SELECT COALESCE(SUM(dcl.quantity), 0) FROM delivery_challan_lines dcl WHERE dcl.order_item_id = oi.id) as line_delivered');

        return DB::query()->fromSub($perLine, 'x')
            ->select('po_number', 'item_name')
            ->selectRaw('SUM(line_ordered) as total_ordered')
            ->selectRaw('SUM(line_delivered) as total_delivered')
            ->groupBy('po_number', 'item_name')
            ->orderBy('po_number')
            ->orderBy('item_name')
            ->get()
            ->map(function ($row) {
                $row->pending = max(0, (int) $row->total_ordered - (int) $row->total_delivered);

                return $row;
            })
            ->filter(fn ($row) => $row->pending > 0)
            ->values();
    }

    /**
     * @param  'portrait'|'landscape'  $orientation
     */
    protected function pdfResponse(string $view, array $data, string $filename, string $orientation = 'portrait'): Response
    {
        $pdf = Pdf::loadView($view, $data)->setPaper('a4', $orientation);

        return $pdf->download($filename);
    }
}
