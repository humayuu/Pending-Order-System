@props(['pending'])
@if ((int) $pending > 0)
    <span class="badge text-bg-warning">{{ (int) $pending }} pending</span>
@else
    <span class="badge text-bg-success"><i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i>Delivered</span>
@endif
