@extends('layouts.app')

@section('title', 'Add client')

@section('content')
<x-page-header title="Add client" subtitle="Add a new client to deliver to." />
<div class="card form-card">
    <form method="post" action="{{ route('clients.store') }}">
        <div class="card-body">
            @csrf
            @include('clients._form', ['client' => null])
        </div>
        <div class="card-footer d-flex flex-column flex-sm-row gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
