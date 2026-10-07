@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<x-page-header title="Orders (PO batches)" subtitle="Purchase order batches and their lines.">
    <x-slot:actions>
        <a href="{{ route('orders.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>New order</a>
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
                    <td><span class="badge text-bg-primary">{{ $order->items_count }} {{ \Illuminate\Support\Str::plural('line', $order->items_count) }}</span></td>
                    <td class="text-body-secondary">{{ $order->created_at->format('M j, Y') }}</td>
                    <td class="text-end">
                        <a href="{{ route('orders.edit', $order) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-pen-to-square me-1"></i>Edit</a>
                        <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye me-1"></i>Open</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-empty-state icon="fa-file-invoice" message="No orders yet.">
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
