@extends('layouts.auth-card')

@section('title', 'Forgot Password')
@section('brand-tagline', 'Recover your account securely and regain access to AssetOne.')
@section('brand-icon', 'bi-shield-lock')

@php
    $sent = (bool) session('status');
    $sentTo = (string) session('reset_email', '');
    // j***@mdpt.gov.my — enough to recognise the address without showing it in full.
    $masked = $sentTo && str_contains($sentTo, '@')
        ? mb_substr($sentTo, 0, 1).str_repeat('*', max(2, mb_strlen(strstr($sentTo, '@', true)) - 1)).strstr($sentTo, '@')
        : '';
@endphp

@section('content')

<div class="auth-header">
    <div class="clock" id="clock"></div>
    <h2>Forgot Password?</h2>
    <p>Enter your email address and we will help you reset your password.</p>
</div>

<div class="stepper" aria-hidden="true">
    <div class="st {{ $sent ? 'done' : 'on' }}"><b>@if($sent)<i class="bi bi-check-lg"></i>@else 1 @endif</b><span>Email</span></div>
    <div class="ln {{ $sent ? 'fill' : '' }}"><i></i></div>
    <div class="st {{ $sent ? 'on' : '' }}"><b>2</b><span>Check inbox</span></div>
    <div class="ln"><i></i></div>
    <div class="st"><b>3</b><span>Reset</span></div>
</div>

@if($sent)
    <div class="text-center mb-3">
        <svg class="ck" viewBox="0 0 52 52" aria-hidden="true"><circle cx="26" cy="26" r="24"/><path d="M15 27l8 8 15-17"/></svg>
    </div>
    @if($masked)<p class="sent-to">Link sent to <b>{{ $masked }}</b></p>@endif
    <div class="alert alert-success py-2 small mt-3">
        <i class="bi bi-check-circle me-2"></i>{{ session('status') }} Please check your inbox and follow the link to reset your password.
    </div>

    <form method="POST" action="{{ route('password.email') }}" id="resendForm" data-own-submit>
        @csrf
        <input type="hidden" name="email" value="{{ $sentTo }}">
        <div class="resend-row">
            <span id="resendText">Didn't get it? Resend in <b id="cd">60</b>s</span>
            <span>
                <button type="submit" class="link-btn" id="resendBtn" disabled>Resend link</button>
                &nbsp;&bull;&nbsp;<a href="{{ route('password.request') }}" class="link-btn text-decoration-none">Use another email</a>
            </span>
        </div>
        <div class="cd-bar"><i id="cdBar"></i></div>
    </form>
@else
    <form method="POST" action="{{ route('password.email') }}" id="forgotForm" novalidate data-own-submit>
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

        <button type="submit" class="btn btn-auth w-100" id="sendBtn"><i class="bi bi-envelope-arrow-up me-2"></i>Send Reset Link</button>
    </form>
@endif

<div class="text-center mt-4">
    <a href="{{ route('login') }}" class="back-login"><i class="bi bi-arrow-left me-2"></i>Back to Login</a>
</div>

@endsection

@push('scripts')
<script>
(function () {
    var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    var form = document.getElementById('forgotForm');
    if (form) {
        var email = document.getElementById('email'), btn = document.getElementById('sendBtn');
        try { var saved = localStorage.getItem('assetoneRememberedEmail'); if (saved && !email.value) email.value = saved; } catch (e) {}
        email.addEventListener('input', function () {
            var ok = EMAIL_RE.test(email.value.trim());
            email.classList.toggle('is-valid', ok); email.classList.toggle('is-invalid', !ok && email.value !== '');
        });
        form.addEventListener('submit', function (e) {
            if (!EMAIL_RE.test(email.value.trim())) {
                e.preventDefault(); email.classList.add('is-invalid'); window.authShake();
                window.authToast('Please enter a valid email address.', 'error'); email.focus();
                return;
            }
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending Reset Link...';
        });
    }

    /* ---------- Resend cooldown ---------- */
    var resend = document.getElementById('resendBtn');
    if (resend) {
        var left = 60, cd = document.getElementById('cd'), bar = document.getElementById('cdBar'), text = document.getElementById('resendText');
        var iv = setInterval(function () {
            left--;
            cd.textContent = Math.max(left, 0);
            bar.style.transform = 'scaleX(' + Math.max(left, 0) / 60 + ')';
            if (left <= 0) { clearInterval(iv); resend.disabled = false; text.textContent = "Didn't get it?"; }
        }, 1000);
        bar.style.transition = 'transform 1s linear';
        document.getElementById('resendForm').addEventListener('submit', function () { resend.disabled = true; resend.textContent = 'Sending...'; });
    }
})();
</script>
@endpush
