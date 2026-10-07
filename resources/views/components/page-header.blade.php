@props(['title', 'subtitle' => null])
<div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-4">
    <div class="min-w-0">
        <h1 class="h3 fw-semibold mb-1">{{ $title }}</h1>
        @if ($subtitle)
            <p class="text-body-secondary mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    @if (isset($actions) && ! $actions->isEmpty())
        <div class="d-flex flex-wrap gap-2 no-print">{{ $actions }}</div>
    @endif
</div>
