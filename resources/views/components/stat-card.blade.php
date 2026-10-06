@props(['label', 'value', 'icon' => 'bi-circle', 'variant' => 'primary'])
<div class="card stat-card h-100">
    <div class="card-body">
        <span class="stat-icon {{ $variant }}"><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
        <div>
            <div class="text-label">{{ $label }}</div>
            <div class="stat-number">{{ $value }}</div>
        </div>
    </div>
</div>
