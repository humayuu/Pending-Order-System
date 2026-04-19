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
        #toast-host { max-width: min(22rem, calc(100vw - 1rem)); }
        @media (max-width: 575.98px) {
            #toast-host { left: 0.5rem !important; right: 0.5rem !important; max-width: none; width: auto !important; }
            #toast-host .toast { width: 100%; }
        }
    </style>
</head>
<body class="bg-light d-flex flex-column min-vh-100">
<a class="visually-hidden-focusable btn btn-sm btn-primary position-fixed top-0 start-0 m-2" style="z-index: 1100" href="#main-content-guest">Skip to main content</a>

<nav class="navbar navbar-dark bg-primary" aria-label="Application header">
    <div class="container">
        <span class="navbar-brand mb-0 h1 fs-5">{{ config('app.name') }}</span>
    </div>
</nav>

<main id="main-content-guest" class="container px-3 px-sm-4 py-4 py-md-5 flex-grow-1" tabindex="-1" role="main">
    @yield('content')
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
