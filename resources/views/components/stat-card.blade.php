@props(['label', 'value', 'icon' => 'fa-circle', 'variant' => 'primary'])
<div class="card shadow-sm h-100">
    <div class="card-body d-flex align-items-center gap-3">
        <span class="stat-icon rounded-3 text-bg-{{ $variant }} d-inline-flex align-items-center justify-content-center fs-4 flex-shrink-0"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
        <div>
            <div class="text-uppercase small fw-semibold text-body-secondary">{{ $label }}</div>
            <div class="fs-3 fw-bold lh-sm">{{ $value }}</div>
        </div>
    </div>
</div>
