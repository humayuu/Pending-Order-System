@php
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'bi-speedometer2'],
        ['label' => 'Clients', 'route' => 'clients.index', 'match' => 'clients.*', 'icon' => 'bi-people'],
        ['label' => 'Orders (PO)', 'route' => 'orders.index', 'match' => 'orders.*', 'icon' => 'bi-receipt'],
        ['label' => 'Delivery challans', 'route' => 'challans.index', 'match' => 'challans.*', 'icon' => 'bi-truck'],
        ['label' => 'Reports', 'route' => 'reports.index', 'match' => 'reports.*', 'icon' => 'bi-bar-chart-line'],
    ];
@endphp
<div class="sidebar-inner">
    <a class="sidebar-brand" href="{{ route('dashboard') }}">
        <span class="brand-mark"><i class="bi bi-box-seam"></i></span>
        <span class="text-truncate">{{ config('app.name') }}</span>
    </a>
    <div class="sidebar-section">Menu</div>
    <ul class="sidebar-nav" aria-label="Primary navigation">
        @foreach ($navItems as $nav)
            @php($active = request()->routeIs($nav['match']))
            <li>
                <a class="sidebar-link {{ $active ? 'active' : '' }}" href="{{ route($nav['route']) }}"
                   @if($active) aria-current="page" @endif>
                    <i class="bi {{ $nav['icon'] }}" aria-hidden="true"></i>
                    <span>{{ $nav['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <span class="avatar">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <div class="min-w-0">
                <div class="fw-semibold small text-truncate">{{ auth()->user()->name }}</div>
                <small>Administrator</small>
            </div>
        </div>
    </div>
</div>
