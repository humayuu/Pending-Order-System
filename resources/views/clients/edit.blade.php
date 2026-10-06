@extends('layouts.app')

@section('title', 'Edit client')

@section('content')
<x-page-header title="Edit client" subtitle="Update client details." />
<div class="card form-card">
    <form method="post" action="{{ route('clients.update', $client) }}">
        <div class="card-body">
            @csrf
            @method('PUT')
            @include('clients._form', ['client' => $client])
        </div>
        <div class="card-footer d-flex flex-column flex-sm-row gap-2">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
