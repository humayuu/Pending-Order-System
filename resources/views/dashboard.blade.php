@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
    <h1 class="h3 mb-0">Dashboard</h1>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Clients</div>
                <div class="stat-number">{{ $clientsCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">PO / order batches</div>
                <div class="stat-number">{{ $ordersCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Delivery challans</div>
                <div class="stat-number">{{ $challansCount }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">Pending quantities by PO line</div>
    <div class="card-body p-0">
        @if ($pendingLines->isEmpty())
            <p class="text-muted p-3 mb-0">Nothing pending. Add an order or deliver against existing lines.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle" aria-label="Pending stock by PO line">
                    <caption class="visually-hidden">Items and PO numbers with remaining quantity to deliver</caption>
                    <thead class="table-light">
                    <tr>
                        <th scope="col">Item</th>
                        <th scope="col">PO number</th>
                        <th scope="col">Order #</th>
                        <th scope="col" class="d-none d-md-table-cell">Notes</th>
                        <th scope="col" class="text-end">Pending</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($pendingLines as $row)
                        @php($item = $row['item'])
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $item->item_name }}</div>
                                <div class="d-md-none small text-muted mt-1">{{ $item->notes ? \Illuminate\Support\Str::limit($item->notes, 60) : '—' }}</div>
                            </td>
                            <td><span class="badge text-bg-secondary">{{ $item->po_number }}</span></td>
                            <td>#{{ $item->order_id }}</td>
                            <td class="small text-break d-none d-md-table-cell">{{ $item->notes ? \Illuminate\Support\Str::limit($item->notes, 40) : '—' }}</td>
                            <td class="text-end fw-semibold">{{ $row['pending'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
