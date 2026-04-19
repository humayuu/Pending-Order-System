@extends('layouts.app')

@section('title', 'Delivery challans')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 mb-4">
    <h1 class="h3 mb-0">Delivery challans</h1>
    <a href="{{ route('challans.create') }}" class="btn btn-primary align-self-stretch align-self-sm-auto">New challan</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
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
                    <td class="font-monospace small text-break">{{ $ch->challan_number }}</td>
                    <td>
                        <div>{{ $ch->client->name }}</div>
                        <div class="d-sm-none small text-muted">{{ $ch->issued_on->format('M j, Y') }}</div>
                    </td>
                    <td class="d-none d-sm-table-cell">{{ $ch->issued_on->format('M j, Y') }}</td>
                    <td class="text-end">{{ (int) ($ch->total_qty ?? 0) }}</td>
                    <td class="text-end">
                        <a href="{{ route('challans.show', $ch) }}" class="btn btn-sm btn-outline-primary">Open</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted p-4">No challans yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($challans->hasPages())
        <div class="card-footer bg-white overflow-auto">{{ $challans->links() }}</div>
    @endif
</div>
@endsection
