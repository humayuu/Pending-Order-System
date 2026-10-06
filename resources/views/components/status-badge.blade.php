@props(['pending'])
@if ((int) $pending > 0)
    <span class="badge-soft badge-soft-warning">{{ (int) $pending }} pending</span>
@else
    <span class="badge-soft badge-soft-success"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Delivered</span>
@endif
