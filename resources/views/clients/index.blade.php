@extends('layouts.app')

@section('title', 'Clients')

@section('content')
<x-page-header title="Clients" subtitle="People and companies you deliver to.">
    <x-slot:actions>
        <a href="{{ route('clients.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Add client</a>
    </x-slot:actions>
</x-page-header>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <caption class="visually-hidden">List of clients with phone, address, and actions</caption>
            <thead>
            <tr>
                <th scope="col">Name</th>
                <th scope="col">Phone</th>
                <th scope="col">Address</th>
                <th scope="col" class="text-end">Orders</th>
                <th scope="col" class="text-end">Challans</th>
                <th scope="col" class="text-end">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($clients as $client)
                <tr>
                    <td class="fw-medium">{{ $client->name }}</td>
                    <td>{{ $client->phone ?: '—' }}</td>
                    <td class="small text-body-secondary text-break">{{ $client->address ? \Illuminate\Support\Str::limit($client->address, 80) : '—' }}</td>
                    <td class="text-end">{{ $client->orders_count }}</td>
                    <td class="text-end">{{ $client->delivery_challans_count }}</td>
                    <td class="text-end">
                        <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                            <a href="{{ route('orders.index', ['client_id' => $client->id]) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-file-invoice me-1"></i>Orders</a>
                            <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-pen-to-square me-1"></i>Edit</a>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    data-app-delete
                                    data-app-delete-url="{{ route('clients.destroy', $client) }}"
                                    data-app-delete-message="Delete client {{ $client->name }}? This cannot be undone.">
                                <i class="fa-solid fa-trash me-1"></i>Delete
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-empty-state icon="fa-users" message="No clients yet.">
                    <a href="{{ route('clients.create') }}" class="btn btn-sm btn-primary">Add client</a>
                </x-empty-state></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if ($clients->hasPages())
        <div class="card-footer overflow-auto">{{ $clients->links() }}</div>
    @endif
</div>
@endsection
