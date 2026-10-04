@extends('layouts.auth-card')

@section('title', 'Login')
@section('brand-tagline', 'Manage, track and monitor organisational assets efficiently in one centralised system.')
@section('brand-icon', 'bi-building-gear')

@section('brand-extra')
    <p class="brand-subtitle typer" id="typer"></p>
@endsection

@section('overlay')
    <div class="login-overlay" id="loginOverlay" aria-hidden="true">
        <div class="overlay-brand"><img src="{{ asset('images/assetone-logo.png') }}" alt="AssetOne"><span>AssetOne</span></div>
        <div><div class="overlay-spinner"></div></div>
        <div class="overlay-text" id="overlayText">Signing you in...<small>Verifying credentials</small></div>
        <div class="progress-track"><div class="progress-bar-fill" id="progressFill"></div></div>
    </div>
@endsection

@section('content')

<div class="auth-header">
    <div class="clock" id="clock"></div>
    <h2 id="greeting" data-greet>Welcome Back</h2>
    <p>Please sign in to access your AssetOne account.</p>
</div>

<form method="POST" action="{{ route('login') }}" id="loginForm" novalidate data-own-submit>
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <div class="input-group has-validation">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="Enter your email address" autocomplete="email" required>
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
        <div class="hint" data-caps-for="passwordField"><i class="bi bi-exclamation-triangle me-1"></i>Caps Lock is on</div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
            <label class="form-check-label" for="rememberMe">Remember me</label>
        </div>
        <a href="{{ route('password.request') }}" class="back-login">Forgot Password?</a>
    </div>

    <button type="submit" class="btn btn-auth w-100" id="loginButton">
        <i class="bi bi-box-arrow-in-right me-2"></i>Login
    </button>
</form>

<div class="lock-banner" id="lockBanner" role="alert" data-seconds="{{ (int) session('lock_seconds', 0) }}"></div>

@endsection

@push('scripts')
<script>
(function () {
    var reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
    var form = document.getElementById('loginForm'), email = document.getElementById('email'), password = document.getElementById('passwordField'),
        remember = document.getElementById('rememberMe'), button = document.getElementById('loginButton'), lock = document.getElementById('lockBanner');
    var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/, KEY = 'assetoneRememberedEmail';

    /* ---------- Typewriter ---------- */
    (function () {
        var lines = ['Manage every asset in one place.', 'Track location, condition and value.', 'Monitor maintenance in real time.', 'Built for Majlis Daerah Perak Tengah.'];
        var el = document.getElementById('typer'), i = 0, c = 0, del = false;
        if (reduced) { el.textContent = lines[0]; return; }
        (function tick() {
            var t = lines[i];
            el.textContent = t.slice(0, c);
            if (!del && c === t.length) { del = true; return setTimeout(tick, 1600); }
            if (del && c === 0) { del = false; i = (i + 1) % lines.length; }
            c += del ? -1 : 1;
            setTimeout(tick, del ? 25 : 55);
        })();
    })();

    /* ---------- Remembered email ---------- */
    try {
        var saved = localStorage.getItem(KEY);
        if (saved && !email.value) { email.value = saved; remember.checked = true; email.classList.add('is-valid'); password.focus(); }
        else if (email.value) password.focus();
        else email.focus();
    } catch (e) { email.focus(); }

    /* ---------- Live validation ---------- */
    function mark(input, ok) {
        input.classList.toggle('is-valid', ok && input.value !== '');
        input.classList.toggle('is-invalid', !ok && input.value !== '');
    }
    email.addEventListener('input', function () { mark(email, EMAIL_RE.test(email.value.trim())); });
    password.addEventListener('input', function () { mark(password, password.value.length > 0); });

    /* ---------- Lock-out countdown ---------- */
    var locked = false, left = parseInt(lock.dataset.seconds, 10) || 0;
    if (left > 0) {
        locked = true; button.disabled = true; lock.classList.add('show');
        var draw = function () { lock.innerHTML = '<i class="bi bi-shield-exclamation me-2"></i>Too many failed attempts. Try again in <b>' + left + 's</b>.'; };
        draw();
        var timer = setInterval(function () {
            left--;
            if (left <= 0) {
                clearInterval(timer); locked = false; button.disabled = false; lock.classList.remove('show');
                window.authToast('You can try signing in again.', 'info');
            } else draw();
        }, 1000);
    }

    /* ---------- Submit ---------- */
    form.addEventListener('submit', function (e) {
        if (locked) { e.preventDefault(); return; }
        var okEmail = EMAIL_RE.test(email.value.trim()), okPass = password.value.length > 0;
        mark(email, okEmail); mark(password, okPass);
        if (!okEmail || !okPass) {
            e.preventDefault();
            if (!email.value) email.classList.add('is-invalid');
            if (!password.value) password.classList.add('is-invalid');
            window.authShake();
            window.authToast('Please fix the highlighted fields.', 'error');
            (okEmail ? password : email).focus();
            return;
        }
        try { remember.checked ? localStorage.setItem(KEY, email.value.trim()) : localStorage.removeItem(KEY); } catch (err) {}

        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Verifying...';
        var overlay = document.getElementById('loginOverlay'), fill = document.getElementById('progressFill');
        overlay.classList.add('active'); overlay.setAttribute('aria-hidden', 'false');
        fill.style.width = '30%';
        setTimeout(function () { fill.style.width = '65%'; }, 500);
        setTimeout(function () { fill.style.width = '90%'; }, 1100);
    });
    // Coming back with the Back button must not leave the overlay showing.
    addEventListener('pageshow', function () {
        document.getElementById('loginOverlay').classList.remove('active');
        if (!locked) { button.disabled = false; button.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Login'; }
    });

    /* ---------- A little confetti after a successful password reset ---------- */
    var status = document.getElementById('authData').dataset.status || '';
    if (/reset/i.test(status) && !reduced) {
        var colors = ['#90caf9', '#34d399', '#fbbf24', '#f472b6', '#a78bfa'];
        for (var n = 0; n < 60; n++) {
            var p = document.createElement('i'); p.className = 'cf';
            p.style.background = colors[n % colors.length];
            p.style.setProperty('--x', ((Math.random() - .5) * 700) + 'px');
            p.style.setProperty('--y', ((Math.random() - .7) * 600) + 'px');
            p.style.setProperty('--r', (Math.random() * 720) + 'deg');
            document.body.appendChild(p);
            (function (node) { setTimeout(function () { node.remove(); }, 1600); })(p);
        }
    }
})();
</script>
@endpush
