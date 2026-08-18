@extends('layouts.auth-card')

@section('title', 'Reset Password')
@section('brand-tagline', 'Create a new secure password to protect your AssetOne account.')
@section('brand-icon', 'bi-shield-lock')

@section('content')

<div class="auth-header">
    <h2>Reset Password</h2>
    <p>Create a new password for your AssetOne account.</p>
</div>

@error('email')
    <div class="alert alert-danger py-2 small">{{ $message }}</div>
@enderror

<form method="POST" action="{{ route('password.store') }}" novalidate>
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
            @else
                <div class="invalid-feedback">Password must be at least 8 characters.</div>
            @enderror
        </div>

        <div class="password-requirements">
            <p>Password requirements:</p>
            <div class="requirement" id="lengthRequirement"><i class="bi bi-circle"></i> At least 8 characters</div>
            <div class="requirement" id="uppercaseRequirement"><i class="bi bi-circle"></i> At least one uppercase letter</div>
            <div class="requirement" id="numberRequirement"><i class="bi bi-circle"></i> At least one number</div>
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
    </div>

    <button type="submit" class="btn btn-auth w-100">
        <i class="bi bi-key me-2"></i>Reset Password
    </button>
</form>

<div class="text-center mt-4">
    <a href="{{ route('login') }}" class="back-login"><i class="bi bi-arrow-left me-2"></i>Back to Login</a>
</div>

@endsection

@push('scripts')
<script>
    const newPassword = document.getElementById('password');
    const confirmPassword = document.getElementById('password_confirmation');
    const confirmPasswordFeedback = document.getElementById('confirmPasswordFeedback');

    const lengthRequirement = document.getElementById('lengthRequirement');
    const uppercaseRequirement = document.getElementById('uppercaseRequirement');
    const numberRequirement = document.getElementById('numberRequirement');

    function updateRequirement(el, isValid) {
        el.classList.toggle('valid', isValid);
        el.querySelector('i').className = isValid ? 'bi bi-check-circle-fill' : 'bi bi-circle';
    }

    newPassword.addEventListener('input', function () {
        const password = newPassword.value;
        updateRequirement(lengthRequirement, password.length >= 8);
        updateRequirement(uppercaseRequirement, /[A-Z]/.test(password));
        updateRequirement(numberRequirement, /[0-9]/.test(password));
        checkPasswordMatch();
    });

    function checkPasswordMatch() {
        if (confirmPassword.value === '') return;
        const matches = newPassword.value === confirmPassword.value;
        confirmPassword.classList.toggle('is-invalid', !matches);
        confirmPassword.classList.toggle('is-valid', matches);
        if (!matches) confirmPasswordFeedback.textContent = 'Passwords do not match.';
    }
    confirmPassword.addEventListener('input', checkPasswordMatch);
</script>
@endpush
