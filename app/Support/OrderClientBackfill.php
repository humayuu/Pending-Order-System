<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class OrderClientBackfill
{
    /**
     * Assign each client-less order to the client its challans were delivered to,
     * but only when that client is unambiguous. Safe to re-run.
     */
    public static function run(): int
    {
        $rows = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('delivery_challan_lines as l', 'l.order_item_id', '=', 'oi.id')
            ->join('delivery_challans as c', 'c.id', '=', 'l.delivery_challan_id')
            ->whereNull('o.client_id')
            ->groupBy('oi.order_id')
            ->havingRaw('COUNT(DISTINCT c.client_id) = 1')
            ->selectRaw('oi.order_id as order_id, MIN(c.client_id) as client_id')
            ->get();

        foreach ($rows as $row) {
            DB::table('orders')
                ->where('id', $row->order_id)
                ->whereNull('client_id')
                ->update(['client_id' => $row->client_id]);
        }

        return $rows->count();
    }
}
