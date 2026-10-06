@extends('layouts.app')

@section('title', 'Item and PO wise report')

@section('content')
<x-page-header title="Item and PO wise report" subtitle="Pending quantity by item (rows) and PO number (columns), per client.">
    <x-slot:actions>
        <a href="{{ route('reports.item-and-po-wise.pdf', $filters->toQuery()) }}" class="btn btn-primary"><i class="bi bi-file-earmark-pdf"></i>Download PDF</a>
    </x-slot:actions>
</x-page-header>

<x-filter-bar :action="route('reports.item-and-po-wise')" :clients="$clients" :filters="$filters" :orders="$orders" :po="true" :status="true" />

@forelse ($blocks as $block)
    <div class="card mb-4">
        <div class="card-header fw-semibold">{{ $block->client_name }}</div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th>Product name</th>
                    @foreach ($block->pos as $po)
                        <th class="text-end">{{ $po }}</th>
                    @endforeach
                    <th class="text-end">Total</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($block->items as $item)
                    <tr>
                        <td>{{ $item->item_name }}</td>
                        @foreach ($block->pos as $po)
                            <td class="text-end num">{{ $item->cells->has($po) ? number_format($item->cells[$po]) : '' }}</td>
                        @endforeach
                        <td class="text-end num fw-semibold">{{ number_format($item->total) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="card"><x-empty-state icon="bi-bar-chart-line" message="No data yet." /></div>
@endforelse
@endsection
