<?php

namespace App\Services;

use App\Models\Client;
use App\Support\StockFilters;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for "pending = ordered − delivered", per client.
 */
class PendingStockService
{
    /**
     * One row per PO line with ordered/delivered/pending, filtered by client, order, PO and PO date.
     * Status is applied by the grouping methods (at group level) or by $filterStatus here.
     */
    public function lines(StockFilters $f, bool $applyStatus = false): Collection
    {
        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('clients as c', 'c.id', '=', 'o.client_id')
            ->select('oi.id', 'oi.item_name', 'oi.po_number', 'oi.notes', 'o.id as order_id', 'o.client_id', 'o.created_at as po_date')
            ->selectRaw('c.name as client_name')
            ->selectRaw('oi.quantity as ordered')
            ->selectRaw('(SELECT COALESCE(SUM(dcl.quantity), 0) FROM delivery_challan_lines dcl WHERE dcl.order_item_id = oi.id) as delivered');

        if ($f->unassigned) {
            $query->whereNull('o.client_id');
        } elseif ($f->clientId) {
            $query->where('o.client_id', $f->clientId);
        }
        if ($f->orderId) {
            $query->where('o.id', $f->orderId);
        }
        if ($f->poNumber) {
            $query->where('oi.po_number', 'like', '%'.$f->poNumber.'%');
        }
        if ($f->from) {
            $query->where('o.created_at', '>=', $f->from->copy()->startOfDay());
        }
        if ($f->to) {
            $query->where('o.created_at', '<=', $f->to->copy()->endOfDay());
        }

        $lines = $query->orderBy('oi.item_name')->orderBy('oi.po_number')->get()->map(function ($row) {
            $row->ordered = (int) $row->ordered;
            $row->delivered = (int) $row->delivered;
            $row->pending = max(0, $row->ordered - $row->delivered);

            return $row;
        });

        return $applyStatus ? $this->byStatus($lines, $f->status) : $lines;
    }

    /** Item wise report rows: grouped by item name. */
    public function itemWise(StockFilters $f): Collection
    {
        $rows = $this->lines($f)
            ->groupBy('item_name')
            ->map(fn (Collection $g, $name) => $this->totals($g, ['item_name' => $name]))
            ->sortKeys()
            ->values();

        return $this->byStatus($rows, $f->status);
    }

    /** Item and PO wise report rows: grouped by client + PO number + item (same PO number for two clients stays separate). */
    public function itemAndPoWise(StockFilters $f): Collection
    {
        $rows = $this->lines($f)
            ->groupBy(fn ($l) => ($l->client_id ?? 0).'|'.$l->po_number.'|'.$l->item_name)
            ->map(fn (Collection $g) => $this->totals($g, [
                'client_id' => $g->first()->client_id,
                'client_name' => $g->first()->client_name,
                'po_number' => $g->first()->po_number,
                'item_name' => $g->first()->item_name,
            ]))
            ->sortBy([['client_name', 'asc'], ['po_number', 'asc'], ['item_name', 'asc']])
            ->values();

        return $this->byStatus($rows, $f->status);
    }

    /**
     * Pivot for the Item x PO report: one block per client, items as rows, PO numbers as columns.
     * Cell value is the pending quantity (per the status filter) of that item on that PO.
     */
    public function itemPoMatrix(StockFilters $f): Collection
    {
        return $this->itemAndPoWise($f)
            ->groupBy(fn ($r) => $r->client_id ?? 0)
            ->map(function (Collection $rows) {
                $pos = $rows->pluck('po_number')->unique()->sort(SORT_NATURAL)->values();
                $items = $rows->groupBy('item_name')->sortKeys(SORT_NATURAL | SORT_FLAG_CASE)->map(function (Collection $g, $name) {
                    $cells = $g->pluck('pending', 'po_number');

                    return (object) ['item_name' => $name, 'cells' => $cells, 'total' => $cells->sum()];
                })->values();

                return (object) [
                    'client_name' => $rows->first()->client_name ?: 'Unassigned',
                    'pos' => $pos,
                    'items' => $items,
                ];
            })
            ->sortBy(fn ($b) => $b->client_name, SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * Per-client summary. Every client appears (even with no orders); an "Unassigned" row is added when needed.
     * Date/PO/order filters narrow the lines counted; the client filter narrows the rows.
     */
    public function clientSummary(StockFilters $f): Collection
    {
        $lines = $this->lines($f)->groupBy(fn ($l) => $l->client_id ?? 0);
        $challans = DB::table('delivery_challans')
            ->selectRaw('client_id, COUNT(*) as n')
            ->groupBy('client_id')
            ->pluck('n', 'client_id');

        $clients = Client::query()
            ->when($f->clientId, fn ($q) => $q->whereKey($f->clientId))
            ->when($f->unassigned, fn ($q) => $q->whereRaw('1 = 0'))
            ->orderBy('name')
            ->get(['id', 'name']);

        $rows = $clients->map(fn ($c) => $this->summaryRow($c->id, $c->name, $lines->get($c->id, collect()), (int) ($challans[$c->id] ?? 0)));

        if (($f->unassigned || ! $f->clientId) && $lines->has(0)) {
            $rows->push($this->summaryRow(null, 'Unassigned', $lines->get(0), 0));
        }

        return $this->byStatus($rows->values(), $f->status, 'pending_qty');
    }

    /** Grand totals for a summary collection. */
    public function overall(Collection $summary): object
    {
        return (object) [
            'orders' => $summary->sum('orders'),
            'open_orders' => $summary->sum('open_orders'),
            'pending_lines' => $summary->sum('pending_lines'),
            'ordered_qty' => $summary->sum('ordered_qty'),
            'delivered_qty' => $summary->sum('delivered_qty'),
            'pending_qty' => $summary->sum('pending_qty'),
        ];
    }

    private function summaryRow(?int $id, string $name, Collection $lines, int $challans): object
    {
        $pendingLines = $lines->where('pending', '>', 0);

        return (object) [
            'client_id' => $id,
            'client_name' => $name,
            'orders' => $lines->pluck('order_id')->unique()->count(),
            'open_orders' => $pendingLines->pluck('order_id')->unique()->count(),
            'pending_lines' => $pendingLines->count(),
            'ordered_qty' => (int) $lines->sum('ordered'),
            'delivered_qty' => (int) $lines->sum('delivered'),
            'pending_qty' => (int) $pendingLines->sum('pending'),
            'challans' => $challans,
        ];
    }

    private function totals(Collection $group, array $attrs): object
    {
        $ordered = (int) $group->sum('ordered');
        $delivered = (int) $group->sum('delivered');

        return (object) ($attrs + [
            'total_ordered' => $ordered,
            'total_delivered' => $delivered,
            'pending' => max(0, $ordered - $delivered),
        ]);
    }

    private function byStatus(Collection $rows, string $status, string $key = 'pending'): Collection
    {
        return match ($status) {
            'pending' => $rows->filter(fn ($r) => $r->{$key} > 0)->values(),
            'completed' => $rows->filter(fn ($r) => $r->{$key} === 0)->values(),
            default => $rows->values(),
        };
    }
}
