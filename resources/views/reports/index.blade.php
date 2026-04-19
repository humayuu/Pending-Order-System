@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<h1 class="h3 mb-3">Reports</h1>
<p class="text-muted mb-4 small lh-base">Open a report or download a PDF. Item wise and Item and PO wise only list rows where quantity is still left to deliver.</p>

<div class="list-group shadow-sm">
    <div class="list-group-item d-flex flex-column flex-sm-row flex-wrap justify-content-between align-items-stretch align-items-sm-center gap-2 gap-sm-3">
        <span><strong>Item wise</strong></span>
        <span class="d-grid d-sm-flex gap-2">
            <a href="{{ route('reports.item-wise') }}" class="btn btn-sm btn-outline-primary">Open</a>
            <a href="{{ route('reports.item-wise.pdf') }}" class="btn btn-sm btn-primary">PDF</a>
        </span>
    </div>
    <div class="list-group-item d-flex flex-column flex-sm-row flex-wrap justify-content-between align-items-stretch align-items-sm-center gap-2 gap-sm-3">
        <span><strong>Item and PO wise</strong></span>
        <span class="d-grid d-sm-flex gap-2">
            <a href="{{ route('reports.item-and-po-wise') }}" class="btn btn-sm btn-outline-primary">Open</a>
            <a href="{{ route('reports.item-and-po-wise.pdf') }}" class="btn btn-sm btn-primary">PDF</a>
        </span>
    </div>
</div>
@endsection
