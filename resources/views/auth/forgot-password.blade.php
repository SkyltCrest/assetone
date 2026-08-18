@extends('layouts.auth-card')

@section('title', 'Forgot Password')
@section('brand-tagline', 'Recover access to your AssetOne account securely and easily.')
@section('brand-icon', 'bi-envelope-lock')

@section('content')

<div class="auth-header">
    <h2>Forgot Password?</h2>
    <p>Enter your registered email address and we will send you a password reset link.</p>
</div>

@if (session('status'))
    <div class="alert alert-success success-message" role="alert">
        <div class="d-flex align-items-start">
            <i class="bi bi-check-circle-fill me-2 mt-1"></i>
            <div><strong>Reset Link Sent</strong><br>{{ session('status') }}<br><br>Please check your inbox and follow the instructions to reset your password.</div>
        </div>
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}" novalidate>
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

    <button type="submit" class="btn btn-auth w-100"><i class="bi bi-envelope me-2"></i>Send Reset Link</button>
</form>

<div class="text-center mt-4">
    <a href="{{ route('login') }}" class="back-login"><i class="bi bi-arrow-left me-2"></i>Back to Login</a>
</div>

@endsection
