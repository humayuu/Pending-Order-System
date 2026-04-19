@extends('layouts.app')

@section('title', 'Clients')

@section('content')
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 mb-4">
    <h1 class="h3 mb-0" id="clients-heading">Clients</h1>
    <a href="{{ route('clients.create') }}" class="btn btn-primary align-self-stretch align-self-sm-auto" aria-describedby="clients-heading">Add client</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" aria-describedby="clients-heading">
            <caption class="visually-hidden">List of clients with phone, address, and actions</caption>
            <thead class="table-light">
            <tr>
                <th scope="col">Name</th>
                <th scope="col">Phone</th>
                <th scope="col">Address</th>
                <th scope="col" class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($clients as $client)
                <tr>
                    <td>{{ $client->name }}</td>
                    <td>{{ $client->phone ?: '—' }}</td>
                    <td class="small text-break">{{ $client->address ? \Illuminate\Support\Str::limit($client->address, 80) : '—' }}</td>
                    <td class="text-end">
                        <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                        <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <button type="button"
                                class="btn btn-sm btn-outline-danger"
                                data-app-delete
                                data-app-delete-url="{{ route('clients.destroy', $client) }}"
                                data-app-delete-message="Delete client {{ $client->name }}? This cannot be undone.">
                            Delete
                        </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-muted p-4">No clients yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($clients->hasPages())
        <div class="card-footer bg-white overflow-auto">{{ $clients->links() }}</div>
    @endif
</div>
@endsection
