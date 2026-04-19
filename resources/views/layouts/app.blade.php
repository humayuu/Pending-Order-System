<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        .navbar-dark .navbar-nav .nav-link.active { font-weight: 600; text-decoration: underline; }
        .navbar-brand { max-width: 60vw; }
        @media (min-width: 992px) {
            .navbar-brand { max-width: none; }
        }
        #toast-host { max-width: min(22rem, calc(100vw - 1rem)); }
        @media (max-width: 575.98px) {
            #toast-host {
                left: 0.5rem !important;
                right: 0.5rem !important;
                max-width: none;
                width: auto !important;
            }
            #toast-host .toast { width: 100%; }
        }
        .table-responsive { -webkit-overflow-scrolling: touch; }
        .stat-number { font-size: clamp(1.65rem, 7vw, 2.5rem); font-weight: 600; line-height: 1.1; }
    </style>
</head>
<body class="bg-light">
<a class="visually-hidden-focusable btn btn-sm btn-primary position-fixed top-0 start-0 m-2" style="z-index: 1100" href="#main-content">Skip to main content</a>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-3 mb-md-4" aria-label="Primary navigation">
    <div class="container px-3 px-sm-4">
        <a class="navbar-brand fw-semibold text-truncate" href="{{ route('dashboard') }}">{{ config('app.name') }}</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain"
                aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation menu">
            <span class="navbar-toggler-icon" aria-hidden="true"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 flex-wrap">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"
                       @if(request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.index') }}"
                       @if(request()->routeIs('clients.*')) aria-current="page" @endif>Clients</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}"
                       @if(request()->routeIs('orders.*')) aria-current="page" @endif>Orders (PO)</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('challans.*') ? 'active' : '' }}" href="{{ route('challans.index') }}"
                       @if(request()->routeIs('challans.*')) aria-current="page" @endif>Delivery challans</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}"
                       @if(request()->routeIs('reports.*')) aria-current="page" @endif>Reports</a>
                </li>
            </ul>
            <form method="post" action="{{ route('logout') }}" class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 ms-lg-3 pt-2 pt-lg-0 border-top border-secondary border-opacity-25 mt-2 mt-lg-0" role="form" aria-label="Sign out">
                @csrf
                <span class="navbar-text text-white small text-truncate text-sm-start" id="nav-user-label">{{ auth()->user()->name }}</span>
                <button class="btn btn-outline-light btn-sm" type="submit" aria-describedby="nav-user-label">Log out</button>
            </form>
        </div>
    </div>
</nav>

<main id="main-content" class="container px-3 px-sm-4 pb-4 pb-md-5" tabindex="-1" role="main">
    @yield('content')
</main>

@include('partials.toast-host')
@include('partials.delete-modal')

@php
    $flashPayload = [];
    if (session('status')) {
        $flashPayload[] = ['variant' => 'success', 'title' => 'Success', 'body' => session('status')];
    }
    foreach ($errors->all() as $message) {
        $flashPayload[] = ['variant' => 'danger', 'title' => 'Something went wrong', 'body' => $message];
    }
@endphp
<script>
    window.__FLASH_TOASTS__ = @json($flashPayload);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<script>
(function () {
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const d = document.createElement('div');
        d.textContent = String(text);
        return d.innerHTML;
    }

    window.showAppToast = function (body, variant, title) {
        variant = variant || 'success';
        title = title || (variant === 'danger' ? 'Error' : 'Success');
        const host = document.getElementById('toast-host');
        if (!host || typeof bootstrap === 'undefined' || !bootstrap.Toast) return;
        const role = variant === 'danger' ? 'alert' : 'status';
        const headerClass = variant === 'danger' ? 'bg-danger text-white' : 'bg-success text-white';
        const el = document.createElement('div');
        el.className = 'toast border-0 shadow';
        el.setAttribute('role', role);
        el.setAttribute('aria-relevant', 'additions text');
        el.innerHTML =
            '<div class="toast-header ' + headerClass + '">' +
            '<strong class="me-auto">' + escapeHtml(title) + '</strong>' +
            '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Dismiss notification"></button>' +
            '</div>' +
            '<div class="toast-body">' + escapeHtml(body) + '</div>';
        host.appendChild(el);
        const toast = new bootstrap.Toast(el, { autohide: true, delay: variant === 'danger' ? 9500 : 6000 });
        el.addEventListener('hidden.bs.toast', function () {
            el.remove();
        });
        toast.show();
    };

    document.addEventListener('DOMContentLoaded', function () {
        (window.__FLASH_TOASTS__ || []).forEach(function (item) {
            window.showAppToast(item.body, item.variant, item.title);
        });

        document.addEventListener('click', function (e) {
            const trigger = e.target.closest('[data-app-delete]');
            if (!trigger) return;
            e.preventDefault();
            const url = trigger.getAttribute('data-app-delete-url');
            const message = trigger.getAttribute('data-app-delete-message') || 'Delete this record? This cannot be undone.';
            const form = document.getElementById('appConfirmDeleteForm');
            const modalEl = document.getElementById('appConfirmDeleteModal');
            if (!form || !modalEl || !url) return;
            form.setAttribute('action', url);
            document.getElementById('appConfirmDeleteBody').textContent = message;
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modalEl.addEventListener('shown.bs.modal', function focusSubmit() {
                var btn = document.getElementById('appConfirmDeleteSubmit');
                if (btn) btn.focus();
            }, { once: true });
            modal.show();
        });
    });
})();
</script>
@stack('scripts')
</body>
</html>
