@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 mb-4">
    <h1 class="h3 mb-0">Orders (PO batches)</h1>
    <a href="{{ route('orders.create') }}" class="btn btn-primary align-self-stretch align-self-sm-auto">New order</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" aria-label="Orders list">
            <caption class="visually-hidden">Purchase orders with line counts and dates</caption>
            <thead class="table-light">
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Lines</th>
                <th scope="col">Created</th>
                <th scope="col" class="text-end">View</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>#{{ $order->id }}</td>
                    <td>{{ $order->items_count }}</td>
                    <td>{{ $order->created_at->format('M j, Y') }}</td>
                    <td class="text-end">
                        <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary">Open</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted p-4">No orders yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($orders->hasPages())
        <div class="card-footer bg-white overflow-auto">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
