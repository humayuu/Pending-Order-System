@extends('layouts.app')

@section('title', 'Delivery challans')

@section('content')
<x-page-header title="Delivery challans" subtitle="Dispatch records issued against PO lines.">
    <x-slot:actions>
        <a href="{{ route('challans.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i>New challan</a>
    </x-slot:actions>
</x-page-header>

<x-filter-bar :action="route('challans.index')" :clients="$clients" :filters="$filters" />

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
            <tr>
                <th scope="col">Challan #</th>
                <th scope="col">Client</th>
                <th scope="col" class="d-none d-sm-table-cell">Date</th>
                <th scope="col" class="text-end">Total qty</th>
                <th scope="col" class="text-end">View</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($challans as $ch)
                <tr>
                    <td><span class="badge-po text-break">{{ $ch->challan_number }}</span></td>
                    <td>
                        <div class="fw-medium">{{ $ch->client->name }}</div>
                        <div class="d-sm-none small text-muted">{{ $ch->issued_on->format('M j, Y') }}</div>
                    </td>
                    <td class="d-none d-sm-table-cell text-muted">{{ $ch->issued_on->format('M j, Y') }}</td>
                    <td class="text-end num fw-semibold">{{ (int) ($ch->total_qty ?? 0) }}</td>
                    <td class="text-end">
                        <a href="{{ route('challans.show', $ch) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i>Open</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-empty-state icon="bi-truck" message="No challans yet.">
                    <a href="{{ route('challans.create') }}" class="btn btn-sm btn-primary">New challan</a>
                </x-empty-state></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($challans->hasPages())
        <div class="card-footer overflow-auto">{{ $challans->links() }}</div>
    @endif
</div>
@endsection
