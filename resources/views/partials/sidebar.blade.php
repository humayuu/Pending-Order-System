@php
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'match' => 'dashboard', 'icon' => 'fa-gauge-high'],
        ['label' => 'Clients', 'route' => 'clients.index', 'match' => 'clients.*', 'icon' => 'fa-users'],
        ['label' => 'Orders (PO)', 'route' => 'orders.index', 'match' => 'orders.*', 'icon' => 'fa-file-invoice'],
        ['label' => 'Delivery challans', 'route' => 'challans.index', 'match' => 'challans.*', 'icon' => 'fa-truck'],
        ['label' => 'Reports', 'route' => 'reports.index', 'match' => 'reports.*', 'icon' => 'fa-chart-line'],
    ];
@endphp
<div class="sidebar-inner d-flex flex-column flex-shrink-0 p-3 text-bg-dark">
    <div class="d-flex align-items-center mb-3 mb-md-0">
        <a class="d-flex align-items-center me-auto text-white text-decoration-none min-w-0" href="{{ route('dashboard') }}">
            <i class="fa-solid fa-boxes-stacked fa-lg me-2" aria-hidden="true"></i>
            <span class="fs-4 text-truncate">{{ config('app.name') }}</span>
        </a>
        <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas"
                data-bs-target="#appSidebar" aria-label="Close navigation menu"></button>
    </div>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto sidebar-nav" aria-label="Primary navigation">
        @foreach ($navItems as $nav)
            @php($active = request()->routeIs($nav['match']))
            <li class="nav-item">
                <a class="nav-link {{ $active ? 'active' : 'text-white' }}" href="{{ route($nav['route']) }}"
                   @if($active) aria-current="page" @endif>
                    <i class="fa-solid fa-fw {{ $nav['icon'] }} me-2" aria-hidden="true"></i>{{ $nav['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
    <hr>
    <div class="d-flex align-items-center text-white">
        <span class="avatar me-2">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
        <div class="min-w-0">
            <strong class="d-block text-truncate">{{ auth()->user()->name }}</strong>
            <small class="text-secondary">Administrator</small>
        </div>
    </div>
</div>
