@extends('layouts.app')

@section('title', 'Item wise report')

@section('content')
<div class="d-flex flex-column flex-sm-row flex-wrap justify-content-between align-items-stretch align-items-sm-center gap-2 mb-4">
    <h1 class="h3 mb-0">Item wise report</h1>
    <span class="d-grid d-sm-flex flex-wrap gap-2">
        <a href="{{ route('reports.item-wise.pdf') }}" class="btn btn-primary">Download PDF</a>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">All reports</a>
    </span>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0 align-middle">
            <thead class="table-light">
            <tr>
                <th>Item</th>
                <th class="text-end">Total ordered</th>
                <th class="text-end">Total delivered</th>
                <th class="text-end">Pending</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->item_name }}</td>
                    <td class="text-end">{{ $row->total_ordered }}</td>
                    <td class="text-end">{{ $row->total_delivered }}</td>
                    <td class="text-end fw-semibold">{{ $row->pending }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted p-4">No data yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
