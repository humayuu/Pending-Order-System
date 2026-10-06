@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="hero-banner">
    <div>
        <h1>Welcome back, {{ auth()->user()->name }}</h1>
        <p>Here is what is happening with your orders and deliveries.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('orders.create') }}" class="btn btn-outline-light"><i class="bi bi-plus-lg"></i>New order</a>
        <a href="{{ route('challans.create') }}" class="btn btn-light"><i class="bi bi-plus-lg"></i>New challan</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <x-stat-card label="Clients" :value="$clientsCount" icon="bi-people" variant="primary" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-stat-card label="PO / order batches" :value="$ordersCount" icon="bi-receipt" variant="info" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-stat-card label="Delivery challans" :value="$challansCount" icon="bi-truck" variant="success" />
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <x-stat-card label="Total pending qty" :value="$overall->pending_qty" icon="bi-hourglass-split" variant="warning" />
    </div>
</div>

@if ($unassignedOrders > 0)
    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2">
        <span><i class="bi bi-exclamation-triangle me-1"></i>{{ $unassignedOrders }} order(s) have no client assigned.</span>
        <a href="{{ route('orders.index', ['client_id' => 'unassigned']) }}" class="btn btn-sm btn-warning">Review</a>
    </div>
@endif

<div class="card mb-4">
    <div class="card-header">
        <span>Client-wise pending summary</span>
        <a href="{{ route('reports.item-and-po-wise') }}" class="btn btn-sm btn-outline-primary">Full report</a>
    </div>
    @if ($clientSummary->isEmpty())
        <x-empty-state icon="bi-people" message="No clients yet." />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>Client</th>
                    <th class="text-end">Orders</th>
                    <th class="text-end d-none d-md-table-cell">Open orders</th>
                    <th class="text-end d-none d-md-table-cell">Pending lines</th>
                    <th class="text-end">Pending qty</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($clientSummary as $row)
                    <tr>
                        <td class="fw-medium">
                            @if ($row->client_id)
                                <a href="{{ route('dashboard', ['client_id' => $row->client_id]) }}" class="text-decoration-none">{{ $row->client_name }}</a>
                            @else
                                {{ $row->client_name }}
                            @endif
                        </td>
                        <td class="text-end num">{{ $row->orders }}</td>
                        <td class="text-end num d-none d-md-table-cell">{{ $row->open_orders }}</td>
                        <td class="text-end num d-none d-md-table-cell">{{ $row->pending_lines }}</td>
                        <td class="text-end"><x-status-badge :pending="$row->pending_qty" /></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="card">
    <div class="card-header">
        <span>Pending quantities by PO line</span>
        <span class="d-flex align-items-center gap-2">
            @if ($filters->clientId || $filters->unassigned)
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">Clear client filter</a>
            @endif
            <span class="badge-soft badge-soft-secondary">{{ $pendingLines->count() }} {{ \Illuminate\Support\Str::plural('line', $pendingLines->count()) }}</span>
        </span>
    </div>
    @if ($pendingLines->isEmpty())
        <x-empty-state icon="bi-check2-circle" message="Nothing pending. Add an order or deliver against existing lines." />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle" aria-label="Pending stock by PO line">
                <caption class="visually-hidden">Items and PO numbers with remaining quantity to deliver</caption>
                <thead>
                <tr>
                    <th scope="col">Item</th>
                    <th scope="col">Client</th>
                    <th scope="col">PO number</th>
                    <th scope="col">Order #</th>
                    <th scope="col" class="d-none d-md-table-cell">Notes</th>
                    <th scope="col" class="text-end">Pending</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($pendingLines as $line)
                    <tr>
                        <td>
                            <div class="fw-medium">{{ $line->item_name }}</div>
                            <div class="d-md-none small text-muted mt-1">{{ $line->notes ? \Illuminate\Support\Str::limit($line->notes, 60) : '—' }}</div>
                        </td>
                        <td>{{ $line->client_name ?: 'Unassigned' }}</td>
                        <td><span class="badge-po">{{ $line->po_number }}</span></td>
                        <td>#{{ $line->order_id }}</td>
                        <td class="small text-muted text-break d-none d-md-table-cell">{{ $line->notes ? \Illuminate\Support\Str::limit($line->notes, 40) : '—' }}</td>
                        <td class="text-end num"><x-status-badge :pending="$line->pending" /></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
