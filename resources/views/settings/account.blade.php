@extends('layouts.app')

@section('title', 'Settings')
@section('heading', 'Settings')
@section('subheading', 'Manage your account information')

@section('content')

<x-banner title="Account Settings" text="Update your profile, keep your account secure and personalise how AssetOne looks." />

<div class="sec-nav animate-in delay-3">
    <div class="pill-tabs" id="secNav">
        <button type="button" class="active" data-t="profileCard"><i class="bi bi-person-badge"></i>Profile</button>
        <button type="button" data-t="photoCard"><i class="bi bi-camera"></i>Photo</button>
        <button type="button" data-t="securityCard"><i class="bi bi-shield-lock"></i>Security</button>
        <button type="button" data-t="appearCard"><i class="bi bi-palette"></i>Appearance</button>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="d-grid gap-4">

            <div class="content-card" id="profileCard">
                <h5 class="fw-bold mb-3"><i class="bi bi-person-badge me-2"></i>Profile Information</h5>
                <form method="POST" action="{{ route('settings.profile.update') }}" id="profileForm">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-medium">Full Name <span class="required">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" minlength="3" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Email <span class="required">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Role</label>
                            <input type="text" value="{{ ucwords(str_replace('_', ' ', $user->role)) }}" class="form-control" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Username</label>
                            <input type="text" value="{{ $user->username }}" class="form-control" disabled>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Department</label>
                        <input type="text" value="{{ $user->department ?: '—' }}" class="form-control" disabled>
                        <div class="form-text">Your role and department are set by an administrator in User Management.</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-2"></i>Save Changes</button>
                        <button type="reset" class="btn btn-secondary px-4" id="cancelProfile">Cancel</button>
                    </div>
                </form>
            </div>

            <div class="content-card" id="securityCard">
                <h5 class="fw-bold mb-3"><i class="bi bi-shield-lock me-2"></i>Change Password</h5>
                <form method="POST" action="{{ route('settings.password.update') }}" id="passwordForm" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="caps-hint d-none" id="capsHint"><i class="bi bi-capslock-fill me-1"></i>Caps Lock is on</div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Current Password <span class="required">*</span></label>
                        <input type="password" name="current_password" id="currentPassword" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">New Password <span class="required">*</span>
                            <button type="button" class="link-sm" id="genPw"><i class="bi bi-magic me-1"></i>Generate strong password</button>
                        </label>
                        <input type="password" name="password" id="newPassword" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" minlength="8" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="error-text" id="newPasswordError">Please meet all password requirements below.</div>
                        <div class="strength-bar-wrapper"><div class="strength-bar" id="strengthBar"></div></div>
                        <div class="strength-label" id="strengthLabel"></div>
                        <div class="password-requirements">
                            <p class="fw-medium">Password requirements:</p>
                            <div class="requirement" id="reqLength"><i class="bi bi-circle"></i> At least 8 characters</div>
                            <div class="requirement" id="reqUpper"><i class="bi bi-circle"></i> At least one uppercase letter</div>
                            <div class="requirement" id="reqLower"><i class="bi bi-circle"></i> At least one lowercase letter</div>
                            <div class="requirement" id="reqNumber"><i class="bi bi-circle"></i> At least one number</div>
                            <div class="requirement" id="reqSpecial"><i class="bi bi-circle"></i> At least one special character (!@#$%^&amp;*)</div>
                            <div class="requirement" id="reqDiffer"><i class="bi bi-circle"></i> Different from current password</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Confirm New Password <span class="required">*</span></label>
                        <input type="password" name="password_confirmation" id="confirmPassword" class="form-control" autocomplete="new-password" required>
                        <div class="error-text" id="confirmPasswordError">Passwords do not match.</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-key me-2"></i>Update Password</button>
                        <button type="reset" class="btn btn-secondary px-4">Cancel</button>
                    </div>
                </form>
            </div>

            <div class="content-card" id="appearCard">
                <h5 class="fw-bold mb-1"><i class="bi bi-palette me-2"></i>Appearance &amp; Eye Comfort</h5>
                <p class="small text-muted mb-3">These choices are remembered on this device only.</p>
                <label class="form-label">Theme</label>
                <div class="theme-opts" id="themeOpts">
                    <button type="button" class="theme-opt" data-theme-set="auto"><span class="sw sw-auto"></span><b>Auto (live)</b><small>Follows the time of day</small></button>
                    <button type="button" class="theme-opt" data-theme-set="light"><span class="sw sw-light"></span><b>Light</b><small>Bright and clear</small></button>
                    <button type="button" class="theme-opt" data-theme-set="dark"><span class="sw sw-dark"></span><b>Dark</b><small>Easy at night</small></button>
                </div>
                <div class="phase-note" id="phaseNote"></div>
                <hr class="my-3" style="border-color:var(--gb);opacity:1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label fw-medium mb-0" for="settingsEyeOn"><i class="bi bi-eye me-2"></i>Eye Comfort</label>
                    <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" role="switch" id="settingsEyeOn" data-eye="on"></div>
                </div>
                <div class="form-text mb-2">Warms the screen colours to cut blue light.</div>
                <label class="small fw-semibold d-flex justify-content-between" for="settingsEyeLevel"><span>Warmth</span><span data-eye="value"></span></label>
                <input type="range" class="form-range" id="settingsEyeLevel" min="0" max="100" step="5" data-eye="level">
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="settingsEyeAuto" data-eye="auto">
                    <label class="form-check-label small" for="settingsEyeAuto">Auto: warmer in the evening &amp; at night</label>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="sticky-col">
            <div class="content-card text-center" id="photoCard">
                <form method="POST" action="{{ route('settings.photo.update') }}" enctype="multipart/form-data" id="photoForm">
                    @csrf
                    <div id="avatarPicker">
                        @include('partials.photo-picker', ['name' => 'photo', 'current' => $user->photoUrl(), 'round' => true, 'maxMb' => 2])
                    </div>
                </form>
                <h5 class="fw-bold mb-0 mt-3">{{ $user->name }}</h5>
                <small class="text-muted">{{ ucwords(str_replace('_', ' ', $user->role)) }}</small>
                <div class="progress mt-3 d-none" id="avatarProgress" style="height:6px"><div class="progress-bar" id="avatarProgressBar" role="progressbar" style="width:0%"></div></div>
                <small class="text-muted d-none" id="avatarProgressText">Uploading... 0%</small>
                @if($user->photo)
                    <form method="POST" action="{{ route('settings.photo.destroy') }}" class="mt-3" data-confirm="Remove your profile photo?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Remove Photo</button>
                    </form>
                @endif
                <small class="text-muted d-block mt-2">JPG or PNG, max 2MB. The picture is saved as soon as you choose it.</small>
                @error('photo')<div class="avatar-error-text show">{{ $message }}</div>@enderror
            </div>

            <div class="content-card" id="cmpCard">
                <h5 class="fw-bold mb-3"><i class="bi bi-award me-2"></i>Profile Completeness</h5>
                <div class="ring">
                    <svg viewBox="0 0 120 120" width="132" height="132">
                        <defs><linearGradient id="ringGrad" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#42a5f5"/><stop offset="1" stop-color="#10b981"/></linearGradient></defs>
                        <circle class="bg" cx="60" cy="60" r="52"/><circle class="fg" id="ringFg" cx="60" cy="60" r="52" data-pct="{{ $completeness['percent'] }}"/>
                    </svg>
                    <div class="ring-num"><span>{{ $completeness['percent'] }}%</span><small>COMPLETE</small></div>
                </div>
                <div class="mt-3">
                    @foreach($completeness['items'] as $label => $done)
                        <div class="cmp-row {{ $done ? 'done' : '' }}"><i class="bi {{ $done ? 'bi-check-lg' : 'bi-circle' }}"></i>{{ $label }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- Completeness ring ---------- */
    var ring = $('ringFg');
    requestAnimationFrame(function () { ring.style.strokeDashoffset = 326.7 * (1 - (+ring.dataset.pct) / 100); });

    /* ---------- Profile photo: saved as soon as it is chosen ---------- */
    $('avatarPicker').addEventListener('photo:change', function (e) {
        if (!e.detail.count) return;
        var bar = $('avatarProgressBar'), txt = $('avatarProgressText'), p = 0;
        $('avatarProgress').classList.remove('d-none'); txt.classList.remove('d-none');
        var iv = setInterval(function () { p = Math.min(p + 12, 90); bar.style.width = p + '%'; txt.textContent = 'Uploading... ' + p + '%'; }, 120);
        $('photoForm').submit();
        addEventListener('pagehide', function () { clearInterval(iv); });
    });

    /* ---------- Password checklist, strength meter, generator, Caps Lock ---------- */
    var current = $('currentPassword'), fresh = $('newPassword'), confirm = $('confirmPassword'), bar = $('strengthBar'), label = $('strengthLabel');
    function mark(id, ok) {
        var el = $(id); el.classList.toggle('valid', ok);
        el.querySelector('i').className = 'bi ' + (ok ? 'bi-check-circle-fill' : 'bi-circle');
        return ok;
    }
    function check() {
        var v = fresh.value;
        var parts = [mark('reqLength', v.length >= 8), mark('reqUpper', /[A-Z]/.test(v)), mark('reqLower', /[a-z]/.test(v)), mark('reqNumber', /[0-9]/.test(v)), mark('reqSpecial', /[^A-Za-z0-9]/.test(v))];
        var differ = mark('reqDiffer', v.length > 0 && v !== current.value), score = parts.filter(Boolean).length;
        var levels = [['', ''], ['Very weak', '#f87171'], ['Weak', '#fb923c'], ['Fair', '#fbbf24'], ['Good', '#34d399'], ['Strong', '#10b981']];
        var level = v ? levels[score] : levels[0];
        bar.style.width = (v ? score * 20 : 0) + '%'; bar.style.background = level[1];
        label.textContent = level[0]; label.style.color = level[1];
        var mismatch = confirm.value !== '' && confirm.value !== v;
        $('confirmPasswordError').classList.toggle('show', mismatch);
        confirm.classList.toggle('is-invalid', mismatch);
        return { all: score === 5 && differ, match: confirm.value === v && v !== '' };
    }
    [current, fresh, confirm].forEach(function (el) { el.addEventListener('input', check); });

    $('passwordForm').addEventListener('submit', function (e) {
        var r = check();
        $('newPasswordError').classList.toggle('show', !r.all);
        if (!current.value || !r.all || !r.match) {
            e.preventDefault();
            (!current.value ? current : !r.all ? fresh : confirm).focus();
        }
    });
    $('passwordForm').addEventListener('reset', function () { setTimeout(function () { check(); $('newPasswordError').classList.remove('show'); }, 0); });

    function genPw() {
        var U = 'ABCDEFGHJKLMNPQRSTUVWXYZ', L = 'abcdefghijkmnopqrstuvwxyz', N = '23456789', S = '!@#$%^&*';
        var r = function (n) { return crypto.getRandomValues(new Uint32Array(1))[0] % n; }, pick = function (x) { return x[r(x.length)]; };
        var a = [pick(U), pick(U), pick(L), pick(L), pick(L), pick(N), pick(N), pick(S), pick(S)], all = U + L + N + S;
        while (a.length < 14) a.push(pick(all));
        for (var i = a.length - 1; i > 0; i--) { var j = r(i + 1), t = a[i]; a[i] = a[j]; a[j] = t; }
        return a.join('');
    }
    $('genPw').addEventListener('click', function () {
        var p = genPw();
        [fresh, confirm].forEach(function (el) { el.type = 'text'; el.value = p; });
        check();
        if (navigator.clipboard) navigator.clipboard.writeText(p).catch(function () {});
        if (window.aoToast) window.aoToast('Strong password generated and copied. Save it somewhere safe.');
    });
    ['keydown', 'keyup'].forEach(function (t) {
        $('passwordForm').addEventListener(t, function (e) { if (e.getModifierState) $('capsHint').classList.toggle('d-none', !e.getModifierState('CapsLock')); });
    });

    $('cancelProfile').addEventListener('click', function () { if (window.aoToast) window.aoToast('Profile changes discarded.'); });

    /* ---------- Theme note ---------- */
    var NAMES = { light: ['bi-sun-fill', 'Morning · Light'], dusk: ['bi-sunset-fill', 'Evening · Dusk'], dark: ['bi-moon-stars-fill', 'Night · Dark'] };
    function note() {
        var ph = NAMES[window.aoTheme ? window.aoTheme() : 'dark'] || NAMES.dark, mode = window.aoThemeMode ? window.aoThemeMode() : 'auto';
        $('phaseNote').innerHTML = '<i class="bi ' + ph[0] + '"></i>Right now: ' + ph[1] + (mode === 'auto' ? ' (auto)' : ' (manual)');
    }
    document.addEventListener('ao:theme', note);
    $('themeOpts').addEventListener('click', function (e) {
        var b = e.target.closest('[data-theme-set]'); if (!b) return;
        setTimeout(function () { note(); if (window.aoToast) window.aoToast('Theme: ' + b.querySelector('b').textContent); }, 0);
    });
    note(); setInterval(note, 30000);
    if (!reduced) document.querySelectorAll('.theme-opt').forEach(function (c) {
        c.addEventListener('mousemove', function (e) { var r = c.getBoundingClientRect(); c.style.transform = 'perspective(600px) rotateY(' + (((e.clientX - r.left) / r.width - .5) * 10) + 'deg) translateY(-3px)'; });
        c.addEventListener('mouseleave', function () { c.style.transform = ''; });
    });

    /* ---------- Section tabs scroll to their card and follow the page ---------- */
    var secs = ['profileCard', 'photoCard', 'securityCard', 'appearCard'];
    $('secNav').addEventListener('click', function (e) {
        var b = e.target.closest('button'); if (b) $(b.dataset.t).scrollIntoView({ behavior: reduced ? 'auto' : 'smooth', block: 'center' });
    });
    if ('IntersectionObserver' in window) {
        var spy = new IntersectionObserver(function (es) {
            es.forEach(function (e) {
                if (!e.isIntersecting) return;
                $('secNav').querySelectorAll('button').forEach(function (b) { b.classList.toggle('active', b.dataset.t === e.target.id); });
            });
        }, { rootMargin: '-35% 0px -55% 0px' });
        secs.forEach(function (id) { spy.observe($(id)); });
    }
})();
</script>
@endpush
