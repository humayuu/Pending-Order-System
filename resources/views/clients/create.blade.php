@extends('layouts.app')

@section('title', 'Add client')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
    <h1 class="h3 mb-0">Add client</h1>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="{{ route('clients.store') }}">
            @csrf
            @include('clients._form', ['client' => null])
            <div class="d-flex flex-column flex-sm-row gap-2 mt-2">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
