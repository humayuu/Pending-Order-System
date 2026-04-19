@extends('layouts.app')

@section('title', 'Order #'.$order->id)

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-0">Order #{{ $order->id }}</h1>
        <p class="text-muted mb-0 small">Created {{ $order->created_at->format('M j, Y') }}</p>
    </div>
    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary align-self-stretch align-self-sm-auto">Back to list</a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">PO lines</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
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
                        <span class="badge text-bg-secondary">{{ $item->po_number }}</span>
                        <div class="d-md-none small text-muted mt-1">{{ $item->notes ?: '—' }}</div>
                        <div class="d-sm-none small text-muted mt-1">Delivered: {{ $delivered }}</div>
                    </td>
                    <td class="small text-break d-none d-md-table-cell">{{ $item->notes ?: '—' }}</td>
                    <td>{{ $item->item_name }}</td>
                    <td class="text-end">{{ $item->quantity }}</td>
                    <td class="text-end d-none d-sm-table-cell">{{ $delivered }}</td>
                    <td class="text-end fw-semibold @if($pending>0) text-warning @endif">{{ $pending }}</td>
                    <td>
                        @if ($item->po_pdf_path)
                            <a href="{{ asset('storage/'.$item->po_pdf_path) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">PDF</a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
