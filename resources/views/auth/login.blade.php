@extends('layouts.guest')

@section('title', 'Log in')

@section('content')
<div class="card shadow">
    <div class="card-body p-4">
        <h1 class="h4 fw-bold mb-1" id="login-heading">Welcome back</h1>
        <p class="text-body-secondary small mb-4" id="login-desc">Sign in with your administrator username and password.</p>
        <form method="post" action="{{ route('login') }}" aria-labelledby="login-heading" aria-describedby="login-desc" novalidate>
            @csrf
            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input type="text" name="username" id="username" value="{{ old('username') }}"
                       class="form-control @error('username') is-invalid @enderror"
                       required autofocus autocomplete="username"
                       aria-required="true"
                       aria-invalid="{{ $errors->has('username') ? 'true' : 'false' }}">
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" name="password" id="password"
                       class="form-control"
                       required autocomplete="current-password"
                       aria-required="true"
                       aria-invalid="false">
            </div>
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-right-to-bracket me-1" aria-hidden="true"></i>Sign in</button>
        </form>
    </div>
</div>
@endsection
