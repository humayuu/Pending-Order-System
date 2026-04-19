@extends('layouts.app')

@section('title', $challan->challan_number)

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-start gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Delivery challan</h1>
        <p class="text-muted mb-0 font-monospace small text-break">{{ $challan->challan_number }}</p>
    </div>
    <a href="{{ route('challans.index') }}" class="btn btn-outline-secondary align-self-stretch align-self-sm-auto">Back</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Client</div>
                <div class="fw-semibold">{{ $challan->client->name }}</div>
                @if ($challan->client->phone)
                    <div class="small mt-1">{{ $challan->client->phone }}</div>
                @endif
                @if ($challan->client->address)
                    <div class="small mt-2 text-break">{{ $challan->client->address }}</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Issue date</div>
                <div>{{ $challan->issued_on->format('F j, Y') }}</div>
                @if ($challan->vehicle_no)
                    <div class="text-muted small mt-2">Vehicle</div>
                    <div>{{ $challan->vehicle_no }}</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Remarks</div>
                <div class="text-break">{{ $challan->remarks ?: '—' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">Dispatched items (by PO)</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
            <tr>
                <th scope="col">PO number</th>
                <th scope="col" class="d-none d-md-table-cell">Notes</th>
                <th scope="col">Item</th>
                <th scope="col" class="d-none d-sm-table-cell">Order #</th>
                <th scope="col" class="text-end">Quantity</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($challan->lines as $line)
                @php($oi = $line->orderItem)
                <tr>
                    <td>
                        <span class="badge text-bg-secondary">{{ $oi->po_number }}</span>
                        <div class="d-md-none small text-muted mt-1">{{ $oi->notes ?: '—' }}</div>
                        <div class="d-sm-none small text-muted mt-1">Order #{{ $oi->order_id }}</div>
                    </td>
                    <td class="small text-break d-none d-md-table-cell">{{ $oi->notes ?: '—' }}</td>
                    <td>{{ $oi->item_name }}</td>
                    <td class="d-none d-sm-table-cell">#{{ $oi->order_id }}</td>
                    <td class="text-end fw-semibold">{{ $line->quantity }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
