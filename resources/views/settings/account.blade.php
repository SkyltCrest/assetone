@extends('layouts.app')

@section('title', 'Settings')
@section('heading', 'Settings')
@section('subheading', 'Manage your account information')

@section('content')

<div class="mb-4">
    <h3 class="fw-bold mb-1">Account Settings</h3>
    <p class="text-muted mb-0">Update your profile, password and how AssetOne looks on this device.</p>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card content-card p-4 text-center mb-4">
            <div class="mx-auto mb-3">@include('partials.avatar', ['user' => $user, 'size' => 96])</div>
            <h5 class="fw-bold mb-0">{{ $user->name }}</h5>
            <p class="text-muted mb-1">{{ ucwords(str_replace('_', ' ', $user->role)) }}</p>
            <p class="small text-muted mb-0">{{ $user->department ?: 'No department set' }}</p>
            @if($user->photo)
                <form method="POST" action="{{ route('settings.photo.destroy') }}" class="mt-3" onsubmit="return confirm('Remove your profile photo?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Remove photo</button>
                </form>
            @endif
        </div>

        <div class="card content-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="fw-bold mb-0">Profile Completeness</h5>
                <span class="fw-bold">{{ $completeness['percent'] }}%</span>
            </div>
            <div class="progress mb-3" style="height:8px;" role="progressbar" aria-valuenow="{{ $completeness['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width:{{ $completeness['percent'] }}%"></div>
            </div>
            @foreach($completeness['items'] as $label => $done)
                <div class="requirement {{ $done ? 'valid' : '' }}"><i class="bi {{ $done ? 'bi-check-circle-fill' : 'bi-circle' }}"></i> {{ $label }}</div>
            @endforeach
            @unless($completeness['items']['Department'])
                <p class="small text-muted mt-2 mb-0">Your department is set by an administrator in User Management.</p>
            @endunless
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card content-card p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-person-badge me-2"></i>Profile Information</h5>
            <form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-medium">Profile Photo</label>
                        @include('partials.photo-picker', ['name' => 'photo', 'current' => $user->photoUrl(), 'round' => true])
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Full Name <span class="required">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" minlength="3" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Email <span class="required">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Username</label>
                        <input type="text" value="{{ $user->username }}" class="form-control" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Role</label>
                        <input type="text" value="{{ ucwords(str_replace('_', ' ', $user->role)) }}" class="form-control" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">Department</label>
                        <input type="text" value="{{ $user->department ?: '—' }}" class="form-control" disabled>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-save px-4"><i class="bi bi-save me-2"></i>Save Changes</button>
                    <button type="reset" class="btn btn-outline-secondary px-4">Cancel</button>
                </div>
            </form>
        </div>

        <div class="card content-card p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-shield-lock me-2"></i>Change Password</h5>
            <form method="POST" action="{{ route('settings.password.update') }}" id="passwordForm">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-medium">Current Password <span class="required">*</span></label>
                        <input type="password" name="current_password" id="currentPassword" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">New Password <span class="required">*</span></label>
                        <input type="password" name="password" id="newPassword" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" minlength="8" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="strength-bar-wrapper"><div class="strength-bar" id="strengthBar"></div></div>
                        <div class="strength-label small" id="strengthLabel"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Confirm New Password <span class="required">*</span></label>
                        <input type="password" name="password_confirmation" id="confirmPassword" class="form-control" autocomplete="new-password" minlength="8" required>
                        <div class="invalid-feedback">Passwords do not match.</div>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted mb-1">A strong password has:</div>
                        <div class="requirement" id="reqLength"><i class="bi bi-circle"></i> At least 8 characters (required)</div>
                        <div class="requirement" id="reqUpper"><i class="bi bi-circle"></i> An uppercase letter</div>
                        <div class="requirement" id="reqLower"><i class="bi bi-circle"></i> A lowercase letter</div>
                        <div class="requirement" id="reqNumber"><i class="bi bi-circle"></i> A number</div>
                        <div class="requirement" id="reqSpecial"><i class="bi bi-circle"></i> A special character (!@#$%^&amp;*)</div>
                        <div class="requirement" id="reqDiffer"><i class="bi bi-circle"></i> Different from your current password (required)</div>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-save px-4"><i class="bi bi-key me-2"></i>Update Password</button>
                </div>
            </form>
        </div>

        <div class="card content-card p-4">
            <h5 class="fw-bold mb-1"><i class="bi bi-palette me-2"></i>Appearance &amp; Eye Comfort</h5>
            <p class="small text-muted mb-3">These choices are remembered on this device only.</p>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Theme</label>
                    <div class="btn-group w-100" role="group" aria-label="Theme">
                        <button type="button" class="btn btn-outline-primary" data-theme-set="auto"><i class="bi bi-magic me-1"></i>Auto</button>
                        <button type="button" class="btn btn-outline-primary" data-theme-set="light"><i class="bi bi-sun-fill me-1"></i>Light</button>
                        <button type="button" class="btn btn-outline-primary" data-theme-set="dark"><i class="bi bi-moon-stars-fill me-1"></i>Dark</button>
                    </div>
                    <div class="form-text">Auto follows the time of day: light in the morning, dusk in the evening, dark at night.</div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label fw-medium mb-0" for="settingsEyeOn">Eye Comfort</label>
                        <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" role="switch" id="settingsEyeOn" data-eye="on"></div>
                    </div>
                    <div class="form-text mb-2">Warms the screen colours to cut blue light.</div>
                    <label class="small fw-semibold d-flex justify-content-between" for="settingsEyeLevel"><span>Warmth</span><span data-eye="value"></span></label>
                    <input type="range" class="form-range" id="settingsEyeLevel" min="0" max="100" step="5" data-eye="level">
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" id="settingsEyeAuto" data-eye="auto">
                        <label class="form-check-label small" for="settingsEyeAuto">Auto: warmer in the evening &amp; at night</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Live password checklist and strength meter.
(function () {
    var current = document.getElementById('currentPassword'),
        fresh = document.getElementById('newPassword'),
        confirm = document.getElementById('confirmPassword'),
        bar = document.getElementById('strengthBar'),
        label = document.getElementById('strengthLabel');

    function mark(id, ok) {
        var el = document.getElementById(id);
        el.classList.toggle('valid', ok);
        el.querySelector('i').className = 'bi ' + (ok ? 'bi-check-circle-fill' : 'bi-circle');
        return ok;
    }

    function update() {
        var v = fresh.value;
        var score = [
            mark('reqLength', v.length >= 8),
            mark('reqUpper', /[A-Z]/.test(v)),
            mark('reqLower', /[a-z]/.test(v)),
            mark('reqNumber', /[0-9]/.test(v)),
            mark('reqSpecial', /[^A-Za-z0-9]/.test(v))
        ].filter(Boolean).length;
        mark('reqDiffer', v.length > 0 && v !== current.value);

        var levels = [['', ''], ['Very weak', '#f87171'], ['Weak', '#fb923c'], ['Fair', '#fbbf24'], ['Good', '#34d399'], ['Strong', '#10b981']];
        var level = v ? levels[score] : levels[0];
        bar.style.width = (v ? score * 20 : 0) + '%';
        bar.style.background = level[1];
        label.textContent = level[0];
        label.style.color = level[1];

        var mismatch = confirm.value !== '' && confirm.value !== v;
        confirm.classList.toggle('is-invalid', mismatch);
        confirm.setCustomValidity(mismatch ? 'Passwords do not match.' : '');
    }

    [current, fresh, confirm].forEach(function (el) { el.addEventListener('input', update); });
})();
</script>
@endpush
