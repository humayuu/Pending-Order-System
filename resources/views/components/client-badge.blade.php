@props(['client' => null])
@if ($client)
    <span class="fw-medium">{{ $client->name }}</span>
@else
    <span class="badge-soft badge-soft-warning"><i class="bi bi-exclamation-circle" aria-hidden="true"></i> Unassigned</span>
@endif
