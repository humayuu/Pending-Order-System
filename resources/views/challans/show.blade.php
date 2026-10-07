@extends('layouts.app')

@section('title', $challan->challan_number)

@section('content')
<x-page-header title="Delivery challan" :subtitle="$challan->challan_number">
    <x-slot:actions>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Print</button>
        <a href="{{ route('challans.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Back</a>
    </x-slot:actions>
</x-page-header>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-uppercase small fw-semibold text-body-secondary mb-1">Client</div>
                <div class="fw-semibold">{{ $challan->client->name }}</div>
                @if ($challan->client->phone)
                    <div class="small mt-1"><i class="fa-solid fa-phone text-body-secondary me-1"></i>{{ $challan->client->phone }}</div>
                @endif
                @if ($challan->client->address)
                    <div class="small mt-2 text-body-secondary text-break">{{ $challan->client->address }}</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-uppercase small fw-semibold text-body-secondary mb-1">Issue date</div>
                <div class="fw-semibold">{{ $challan->issued_on->format('F j, Y') }}</div>
                @if ($challan->vehicle_no)
                    <div class="text-uppercase small fw-semibold text-body-secondary mt-3 mb-1">Vehicle</div>
                    <div>{{ $challan->vehicle_no }}</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="text-uppercase small fw-semibold text-body-secondary mb-1">Remarks</div>
                <div class="text-break">{{ $challan->remarks ?: '—' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Dispatched items (by PO)</div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
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
                        <span class="badge text-bg-light border font-monospace">{{ $oi->po_number }}</span>
                        <div class="d-md-none small text-body-secondary mt-1">{{ $oi->notes ?: '—' }}</div>
                        <div class="d-sm-none small text-body-secondary mt-1">Order #{{ $oi->order_id }}</div>
                    </td>
                    <td class="small text-body-secondary text-break d-none d-md-table-cell">{{ $oi->notes ?: '—' }}</td>
                    <td class="fw-medium">{{ $oi->item_name }}</td>
                    <td class="d-none d-sm-table-cell">#{{ $oi->order_id }}</td>
                    <td class="text-end fw-semibold">{{ $line->quantity }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
