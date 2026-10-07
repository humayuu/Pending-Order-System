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
<a class="visually-hidden-focusable btn btn-sm btn-primary position-fixed top-0 start-0 m-2" style="z-index: 1100" href="#main-content">Skip to main content</a>

<div class="app-shell">
    <aside class="app-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="appSidebar" aria-label="Sidebar">
        @include('partials.sidebar')
    </aside>

    <div class="app-main">
        <header class="app-header bg-body border-bottom shadow-sm px-3 px-md-4">
            <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#appSidebar" aria-controls="appSidebar" aria-label="Open navigation menu">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
            <h2 class="h5 fw-semibold mb-0 text-truncate">@yield('title', config('app.name'))</h2>
            <form method="post" action="{{ route('logout') }}" class="ms-auto">
                @csrf
                <button class="btn btn-primary btn-sm" type="submit" aria-label="Log out"><i class="fa-solid fa-right-from-bracket me-1" aria-hidden="true"></i>Log out</button>
            </form>
        </header>

        <main id="main-content" class="app-content p-3 p-md-4" tabindex="-1" role="main">
            @yield('content')
        </main>
    </div>
</div>

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
