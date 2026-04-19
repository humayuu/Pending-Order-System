@extends('layouts.guest')

@section('title', 'Log in')

@section('content')
<div class="row justify-content-center px-1">
    <div class="col-12 col-sm-10 col-md-8 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3" id="login-heading">Admin login</h1>
                <p class="text-muted small mb-4" id="login-desc">Sign in with your administrator email and password.</p>
                <form method="post" action="{{ route('login') }}" aria-labelledby="login-heading" aria-describedby="login-desc" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror"
                               required autofocus autocomplete="username"
                               aria-required="true"
                               aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" name="password" id="password"
                               class="form-control"
                               required autocomplete="current-password"
                               aria-required="true"
                               aria-invalid="false">
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Sign in</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
