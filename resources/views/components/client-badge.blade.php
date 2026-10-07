@props(['client' => null])
@if ($client)
    <span class="fw-medium">{{ $client->name }}</span>
@else
    <span class="badge text-bg-warning"><i class="fa-solid fa-circle-exclamation me-1" aria-hidden="true"></i>Unassigned</span>
@endif
