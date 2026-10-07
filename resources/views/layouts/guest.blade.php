<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
</head>
<body>
<a class="visually-hidden-focusable btn btn-sm btn-primary position-fixed top-0 start-0 m-2" style="z-index: 1100" href="#main-content-guest">Skip to main content</a>

<main id="main-content-guest" class="auth-wrap bg-body-tertiary" tabindex="-1" role="main">
    <div class="auth-card">
        <div class="text-center mb-4">
            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary text-white fs-3 mb-2" style="width:3.5rem;height:3.5rem"><i class="fa-solid fa-boxes-stacked"></i></span>
            <div class="fw-bold fs-4">{{ config('app.name') }}</div>
        </div>
        @yield('content')
    </div>
</main>

@include('partials.toast-host')

@php
    $guestFlash = [];
    if (session('status')) {
        $guestFlash[] = ['variant' => 'success', 'title' => 'Signed out', 'body' => session('status')];
    }
    foreach ($errors->all() as $message) {
        $guestFlash[] = ['variant' => 'danger', 'title' => 'Sign in failed', 'body' => $message];
    }
@endphp
<script>
    window.__FLASH_TOASTS__ = @json($guestFlash);
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
        el.innerHTML =
            '<div class="toast-header ' + headerClass + '">' +
            '<strong class="me-auto">' + escapeHtml(title) + '</strong>' +
            '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Dismiss notification"></button>' +
            '</div>' +
            '<div class="toast-body">' + escapeHtml(body) + '</div>';
        host.appendChild(el);
        const toast = new bootstrap.Toast(el, { autohide: true, delay: variant === 'danger' ? 10000 : 6000 });
        el.addEventListener('hidden.bs.toast', function () { el.remove(); });
        toast.show();
    };
    document.addEventListener('DOMContentLoaded', function () {
        (window.__FLASH_TOASTS__ || []).forEach(function (item) {
            window.showAppToast(item.body, item.variant, item.title);
        });
    });
})();
</script>
</body>
</html>
