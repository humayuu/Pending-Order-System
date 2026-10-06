@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<x-page-header title="Orders (PO batches)" subtitle="Purchase order batches and their lines.">
    <x-slot:actions>
        <a href="{{ route('orders.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i>New order</a>
    </x-slot:actions>
</x-page-header>

<x-filter-bar :action="route('orders.index')" :clients="$clients" :filters="$filters" :unassigned="true" />

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle" aria-label="Orders list">
            <caption class="visually-hidden">Purchase orders with line counts and dates</caption>
            <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Client</th>
                <th scope="col">Lines</th>
                <th scope="col">Created</th>
                <th scope="col" class="text-end">View</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td class="fw-medium">#{{ $order->id }}</td>
                    <td><x-client-badge :client="$order->client" /></td>
                    <td><span class="badge-soft badge-soft-primary">{{ $order->items_count }} {{ \Illuminate\Support\Str::plural('line', $order->items_count) }}</span></td>
                    <td class="text-muted">{{ $order->created_at->format('M j, Y') }}</td>
                    <td class="text-end">
                        <a href="{{ route('orders.edit', $order) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i>Edit</a>
                        <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i>Open</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-empty-state icon="bi-receipt" message="No orders yet.">
                    <a href="{{ route('orders.create') }}" class="btn btn-sm btn-primary">New order</a>
                </x-empty-state></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($orders->hasPages())
        <div class="card-footer overflow-auto">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
