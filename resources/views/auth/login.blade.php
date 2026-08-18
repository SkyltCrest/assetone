@extends('layouts.auth-card')

@section('title', 'Login')
@section('brand-tagline', 'Manage, track and monitor organisational assets efficiently in one centralised system.')
@section('brand-icon', 'bi-building-gear')

@section('content')

<div class="auth-header">
    <h2>Welcome Back</h2>
    <p>Please sign in to access your AssetOne account.</p>
</div>

@if (session('status'))
    <div class="alert alert-success py-2 small">{{ session('status') }}</div>
@endif

<form method="POST" action="{{ route('login') }}" novalidate>
    @csrf

    <div class="mb-4">
        <label for="email" class="form-label">Email Address</label>
        <div class="input-group has-validation">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="Enter your email address" autocomplete="email" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div class="invalid-feedback">Please enter a valid email address.</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <label for="passwordField" class="form-label">Password</label>
        <div class="input-group has-validation">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="passwordField" class="form-control @error('password') is-invalid @enderror" placeholder="Enter your password" autocomplete="current-password" required>
            <button type="button" class="btn password-toggle" data-toggle-target="passwordField" data-toggle-label="password" aria-label="Show password">
                <i class="bi bi-eye"></i>
            </button>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div class="invalid-feedback">Please enter your password.</div>
            @enderror
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
            <label class="form-check-label" for="rememberMe">Remember Me</label>
        </div>
        <a href="{{ route('password.request') }}" class="back-login">Forgot Password?</a>
    </div>

    <button type="submit" class="btn btn-auth w-100">
        <i class="bi bi-box-arrow-in-right me-2"></i>Login
    </button>
</form>

@endsection
