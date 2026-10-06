@extends('layouts.app')

@section('title', 'Edit order #'.$order->id)

@section('content')
<x-page-header :title="'Edit order #'.$order->id" subtitle="Change the client, reference or notes. PO lines are not editable here." />
<div class="card form-card">
    <form method="post" action="{{ route('orders.update', $order) }}">
        <div class="card-body">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label" for="client_id">Client</label>
                <select name="client_id" id="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                    <option value="">Select client…</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected(old('client_id', $order->client_id) == $client->id)>{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" for="reference">Reference <span class="text-muted fw-normal">(optional)</span></label>
                <input type="text" name="reference" id="reference" class="form-control" value="{{ old('reference', $order->reference) }}">
            </div>
            <div class="mb-3">
                <label class="form-label" for="notes">Notes <span class="text-muted fw-normal">(optional)</span></label>
                <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $order->notes) }}</textarea>
            </div>
        </div>
        <div class="card-footer d-flex flex-column flex-sm-row gap-2">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
