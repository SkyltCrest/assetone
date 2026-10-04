@extends('layouts.auth-card')

@section('title', 'Reset Password')
@section('brand-tagline', 'Create a new secure password to protect your AssetOne account.')
@section('brand-icon', 'bi-shield-lock')

@section('content')

<div class="auth-header">
    <div class="clock" id="clock"></div>
    <h2>Reset Password</h2>
    <p>Create a new password for your AssetOne account.</p>
</div>

<div class="stepper" aria-hidden="true">
    <div class="st done"><b><i class="bi bi-check-lg"></i></b><span>Email</span></div>
    <div class="ln fill"><i></i></div>
    <div class="st done"><b><i class="bi bi-check-lg"></i></b><span>Check inbox</span></div>
    <div class="ln fill"><i></i></div>
    <div class="st on"><b>3</b><span>Reset</span></div>
</div>

@error('email')
    <div class="alert alert-danger py-2 small">{{ $message }}</div>
@enderror

<form method="POST" action="{{ route('password.store') }}" id="resetForm" novalidate data-own-submit>
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ old('email', $email) }}">

    <div class="mb-3">
        <label for="password" class="form-label">New Password</label>
        <div class="input-group has-validation">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter your new password" autocomplete="new-password" minlength="8" required>
            <button type="button" class="btn password-toggle" data-toggle-target="password" data-toggle-label="new password" aria-label="Show new password">
                <i class="bi bi-eye"></i>
            </button>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="hint" data-caps-for="password"><i class="bi bi-exclamation-triangle me-1"></i>Caps Lock is on</div>

        <div class="strength-bar-wrapper"><div class="strength-bar" id="strengthBar"></div></div>
        <div class="strength-label" id="strengthLabel"></div>

        <div class="gen-row">
            <button type="button" class="btn btn-sm" id="genBtn"><i class="bi bi-magic me-1"></i>Generate strong password</button>
            <button type="button" class="btn btn-sm" id="copyBtn"><i class="bi bi-clipboard me-1"></i>Copy</button>
        </div>

        <div class="password-requirements">
            <p>Password requirements:</p>
            <div class="requirement" id="lengthRequirement"><i class="bi bi-circle"></i> At least 8 characters</div>
            <div class="requirement" id="uppercaseRequirement"><i class="bi bi-circle"></i> At least one uppercase letter</div>
            <div class="requirement" id="lowercaseRequirement"><i class="bi bi-circle"></i> At least one lowercase letter</div>
            <div class="requirement" id="numberRequirement"><i class="bi bi-circle"></i> At least one number</div>
            <div class="requirement" id="specialRequirement"><i class="bi bi-circle"></i> At least one special character</div>
        </div>
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label">Confirm New Password</label>
        <div class="input-group has-validation">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirm your new password" autocomplete="new-password" required>
            <button type="button" class="btn password-toggle" data-toggle-target="password_confirmation" data-toggle-label="confirm password" aria-label="Show confirm password">
                <i class="bi bi-eye"></i>
            </button>
            <div class="invalid-feedback" id="confirmPasswordFeedback">Please confirm your password.</div>
        </div>
        <div class="hint" data-caps-for="password_confirmation"><i class="bi bi-exclamation-triangle me-1"></i>Caps Lock is on</div>
    </div>

    <button type="submit" class="btn btn-auth w-100" id="resetBtn">
        <i class="bi bi-key me-2"></i>Reset Password
    </button>
</form>

<div class="text-center mt-4">
    <a href="{{ route('login') }}" class="back-login"><i class="bi bi-arrow-left me-2"></i>Back to Login</a>
</div>

@endsection

@push('scripts')
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var fresh = $('password'), confirm = $('password_confirmation'), feedback = $('confirmPasswordFeedback'), bar = $('strengthBar'), label = $('strengthLabel');

    function req(id, ok) {
        var el = $(id); el.classList.toggle('valid', ok);
        el.querySelector('i').className = ok ? 'bi bi-check-circle-fill' : 'bi bi-circle';
        return ok;
    }
    function check() {
        var v = fresh.value;
        var parts = [req('lengthRequirement', v.length >= 8), req('uppercaseRequirement', /[A-Z]/.test(v)), req('lowercaseRequirement', /[a-z]/.test(v)),
                     req('numberRequirement', /[0-9]/.test(v)), req('specialRequirement', /[^A-Za-z0-9]/.test(v))];
        var score = parts.filter(Boolean).length;
        var levels = [['', ''], ['Very weak', '#f87171'], ['Weak', '#fb923c'], ['Fair', '#fbbf24'], ['Good', '#34d399'], ['Strong', '#10b981']];
        var level = v ? levels[score] : levels[0];
        bar.style.width = (v ? score * 20 : 0) + '%'; bar.style.background = level[1];
        label.textContent = level[0]; label.style.color = level[1];
        return score === 5;
    }
    function match() {
        if (confirm.value === '') return false;
        var ok = fresh.value === confirm.value;
        confirm.classList.toggle('is-invalid', !ok); confirm.classList.toggle('is-valid', ok);
        if (!ok) feedback.textContent = 'Passwords do not match.';
        return ok;
    }
    fresh.addEventListener('input', function () { check(); match(); });
    confirm.addEventListener('input', match);

    /* ---------- Strong password generator + copy ---------- */
    function generate() {
        var U = 'ABCDEFGHJKLMNPQRSTUVWXYZ', L = 'abcdefghijkmnopqrstuvwxyz', N = '23456789', S = '!@#$%^&*';
        var r = function (n) { return crypto.getRandomValues(new Uint32Array(1))[0] % n; }, pick = function (x) { return x[r(x.length)]; };
        var a = [pick(U), pick(U), pick(L), pick(L), pick(L), pick(N), pick(N), pick(S), pick(S)], all = U + L + N + S;
        while (a.length < 14) a.push(pick(all));
        for (var i = a.length - 1; i > 0; i--) { var j = r(i + 1), t = a[i]; a[i] = a[j]; a[j] = t; }
        return a.join('');
    }
    $('genBtn').addEventListener('click', function () {
        var p = generate();
        [fresh, confirm].forEach(function (el) { el.type = 'text'; el.value = p; });
        check(); match();
        window.authToast('Strong password generated. Copy it before you continue.', 'success');
    });
    $('copyBtn').addEventListener('click', function () {
        if (!fresh.value) { window.authToast('Nothing to copy yet.', 'info'); return; }
        if (!navigator.clipboard) { window.authToast('Copy failed. Select the text and copy manually.', 'error'); return; }
        navigator.clipboard.writeText(fresh.value)
            .then(function () { window.authToast('Password copied to clipboard.', 'success'); })
            .catch(function () { window.authToast('Copy failed. Select the text and copy manually.', 'error'); });
    });

    $('resetForm').addEventListener('submit', function (e) {
        var problem = !fresh.value || !confirm.value ? 'Please complete all fields.'
            : fresh.value !== confirm.value ? 'Passwords do not match.'
            : !check() ? 'Password does not meet all requirements.' : '';
        if (problem) { e.preventDefault(); window.authShake(); window.authToast(problem, 'error'); return; }
        var btn = $('resetBtn'); btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Resetting Password...';
    });
})();
</script>
@endpush
