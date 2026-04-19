@extends('layouts.app')

@section('title', 'Edit client')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
    <h1 class="h3 mb-0">Edit client</h1>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="{{ route('clients.update', $client) }}">
            @csrf
            @method('PUT')
            @include('clients._form', ['client' => $client])
            <div class="d-flex flex-column flex-sm-row gap-2 mt-2">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
