@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
@php
    $totalOrdered = $order->items->sum('quantity');
    $totalDelivered = $order->items->sum(fn ($i) => (int) ($i->delivered_sum ?? 0));
    $totalPending = $order->items->sum(fn ($i) => max(0, $i->quantity - (int) ($i->delivered_sum ?? 0)));
@endphp
<x-page-header :title="$pageTitle" :subtitle="($order->client ? $order->client->name.' · ' : '').'Created '.$order->created_at->format('M j, Y')">
    <x-slot:actions>
        @if ($order->items->isNotEmpty())
            <a href="{{ route('orders.po-report', $order) }}" class="btn btn-outline-primary"><i class="fa-solid fa-file-pdf me-1"></i>PDF report</a>
        @endif
        <a href="{{ route('orders.edit', $order) }}" class="btn btn-outline-secondary"><i class="fa-solid fa-pen-to-square me-1"></i>Edit</a>
        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Back to list</a>
    </x-slot:actions>
</x-page-header>

@unless ($order->client)
    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2">
        <span><i class="fa-solid fa-triangle-exclamation me-1 me-1"></i>This order has no client. It cannot be dispatched until one is assigned.</span>
        <a href="{{ route('orders.edit', $order) }}" class="btn btn-sm btn-warning">Assign client</a>
    </div>
@endunless

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <x-stat-card label="Ordered" :value="$totalOrdered" icon="fa-boxes-stacked" variant="primary" />
    </div>
    <div class="col-6 col-md-4">
        <x-stat-card label="Delivered" :value="$totalDelivered" icon="fa-truck" variant="success" />
    </div>
    <div class="col-6 col-md-4">
        <x-stat-card label="Pending" :value="$totalPending" icon="fa-hourglass-half" variant="warning" />
    </div>
</div>

<div class="card">
    <div class="card-header">PO lines</div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
            <tr>
                <th scope="col">PO number</th>
                <th scope="col" class="d-none d-md-table-cell">Notes</th>
                <th scope="col">Item</th>
                <th scope="col" class="text-end text-nowrap">Ordered</th>
                <th scope="col" class="text-end text-nowrap d-none d-sm-table-cell">Delivered</th>
                <th scope="col" class="text-end text-nowrap">Pending</th>
                <th scope="col" class="text-nowrap">PO PDF</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($order->items as $item)
                @php
                    $delivered = (int) ($item->delivered_sum ?? 0);
                    $pending = max(0, $item->quantity - $delivered);
                @endphp
                <tr>
                    <td>
                        <span class="badge text-bg-light border font-monospace">{{ $item->po_number }}</span>
                        <div class="d-md-none small text-body-secondary mt-1">{{ $item->notes ?: '—' }}</div>
                        <div class="d-sm-none small text-body-secondary mt-1">Delivered: {{ $delivered }}</div>
                    </td>
                    <td class="small text-body-secondary text-break d-none d-md-table-cell">{{ $item->notes ?: '—' }}</td>
                    <td class="fw-medium">
                        @if ($item->image_path)
                            <a href="{{ asset('storage/'.$item->image_path) }}" target="_blank" rel="noopener noreferrer"><img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->item_name }}" class="rounded border me-2" style="height:40px;width:40px;object-fit:cover"></a>
                        @endif
                        {{ $item->item_name }}
                    </td>
                    <td class="text-end">{{ $item->quantity }}</td>
                    <td class="text-end d-none d-sm-table-cell">{{ $delivered }}</td>
                    <td class="text-end"><x-status-badge :pending="$pending" /></td>
                    <td>
                        @if ($item->po_pdf_path)
                            <a href="{{ asset('storage/'.$item->po_pdf_path) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-file-pdf me-1"></i>PDF</a>
                        @else
                            <span class="text-body-secondary">—</span>
                        @endif
                    </td>
                </tr>
                <tr class="table-light">
                    <td colspan="7" class="pt-1 pb-3">
                        @php($deliveries = $item->deliveryChallanLines->sortBy([['deliveryChallan.issued_on', 'asc'], ['delivery_challan_id', 'asc']]))
                        <div class="small fw-semibold text-body-secondary mb-1"><i class="fa-solid fa-truck me-1"></i>Delivery history &middot; PO {{ $item->po_number }}</div>
                        @if ($deliveries->isEmpty())
                            <div class="small text-body-secondary">No deliveries yet.</div>
                        @else
                            <table class="table table-sm table-borderless mb-1 small bg-transparent">
                                <thead>
                                <tr class="text-body-secondary">
                                    <th scope="col" class="fw-medium">DC No.</th>
                                    <th scope="col" class="fw-medium">Date</th>
                                    <th scope="col" class="fw-medium d-none d-sm-table-cell">Vehicle</th>
                                    <th scope="col" class="fw-medium text-end">Qty</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($deliveries as $line)
                                    <tr>
                                        <td><a href="{{ route('challans.show', $line->deliveryChallan) }}" class="badge text-bg-light border font-monospace text-decoration-none">{{ $line->deliveryChallan->challan_number }}</a></td>
                                        <td class="text-nowrap">{{ $line->deliveryChallan->issued_on->format('M j, Y') }}</td>
                                        <td class="d-none d-sm-table-cell">{{ $line->deliveryChallan->vehicle_no ?: '—' }}</td>
                                        <td class="text-end fw-semibold">{{ $line->quantity }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <div class="small text-body-secondary">Delivered {{ $delivered }} of {{ $item->quantity }} &middot; Pending {{ $pending }}</div>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
