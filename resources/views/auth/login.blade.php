@extends('layouts.auth-card')

@section('title', 'Login')
@section('brand-tagline', 'Manage, track and monitor organisational assets efficiently in one centralised system.')
@section('brand-icon', 'bi-building-gear')

@push('styles')
<style>
.typer{min-height:52px;font-size:15px;color:#bbdefb;position:relative;z-index:2}
.typer::after{content:"";display:inline-block;width:2px;height:1em;margin-left:3px;vertical-align:-2px;background:#90caf9;animation:blink 1s steps(1) infinite}
@keyframes blink{50%{opacity:0}}
.stats{display:flex;gap:10px;margin-top:22px;position:relative;z-index:2}
.stat{padding:8px 14px;border-radius:12px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);font-size:12px;transition:transform .3s cubic-bezier(.34,1.56,.64,1),background .3s}
.stat:hover{transform:translateY(-4px) scale(1.06);background:rgba(255,255,255,.2)}
.stat i{color:#90caf9;margin-right:5px}
.clock{font-size:12px;color:#90caf9;font-weight:600;margin-bottom:6px}
.form-check-label{color:rgba(255,255,255,.75);font-size:14px}
.form-check-input{background-color:rgba(255,255,255,.1);border-color:rgba(255,255,255,.35)}
.form-check-input:checked{background-color:#1565c0;border-color:#1565c0}
.forgot-password{position:relative;color:#90caf9;text-decoration:none;font-size:14px;font-weight:600;transition:all .3s}
.forgot-password:hover{color:#bbdefb;text-decoration:none}
.forgot-password::after{content:"";position:absolute;left:0;bottom:-4px;width:0;height:2px;background:#90caf9;transition:width .3s}
.forgot-password:hover::after{width:100%}
.forgot-password.clicked{transform:translateX(5px);opacity:.6}
.lock-banner{display:none;margin-top:14px;padding:10px 14px;border-radius:12px;font-size:14px;background:rgba(244,67,54,.18);border:1px solid rgba(255,138,128,.4);color:#ffcdd2}
.lock-banner.show{display:block}

/* Sign-in overlay */
.login-overlay{position:fixed;inset:0;z-index:9999;display:flex;flex-direction:column;justify-content:center;align-items:center;gap:18px;background:radial-gradient(circle at 30% 30%,rgba(33,150,243,.35),transparent 40%),linear-gradient(135deg,#06111f,#0b2038 50%,#0d47a1);opacity:0;visibility:hidden;transition:opacity .45s,visibility .45s}
.login-overlay.active{opacity:1;visibility:visible}
.overlay-brand{position:absolute;top:32px;display:flex;align-items:center;gap:10px;font-weight:600;opacity:.85}
.overlay-brand img{height:32px}
.overlay-spinner{width:58px;height:58px;border:4px solid rgba(255,255,255,.18);border-top-color:#fff;border-radius:50%;animation:spin .8s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.overlay-text{text-align:center;font-size:1.05rem;font-weight:500}
.overlay-text small{display:block;margin-top:6px;font-size:.8rem;opacity:.65}
.progress-track{width:240px;height:5px;border-radius:5px;background:rgba(255,255,255,.15);overflow:hidden}
.progress-bar-fill{height:100%;width:0;background:linear-gradient(90deg,#90caf9,#34d399);transition:width .6s ease}
@media(max-width:767px){.stats{display:none}}
</style>
@endpush

@section('overlay')
<div class="login-overlay" id="loginOverlay" aria-hidden="true">
    <div class="overlay-brand"><img src="{{ asset('images/assetone-logo.png') }}" alt="AssetOne"><span>AssetOne</span></div>
    <div class="overlay-spinner"></div>
    <div class="overlay-text">Signing you in...<small>Verifying credentials</small></div>
    <div class="progress-track"><div class="progress-bar-fill" id="progressFill"></div></div>
</div>
@endsection

@section('brand-before-icon')
    <p class="brand-subtitle typer" id="typer"></p>
@endsection

@section('brand-after-icon')
    <div class="stats">
        <span class="stat"><i class="bi bi-shield-lock"></i>Secure</span>
        <span class="stat"><i class="bi bi-lightning-charge"></i>Real-time</span>
        <span class="stat"><i class="bi bi-diagram-3"></i>Centralised</span>
    </div>
@endsection

@section('content')

<div class="auth-header">
    <div class="clock" id="clock"></div>
    <h2 id="greeting">Welcome Back</h2>
    <p>Please sign in to access your AssetOne account.</p>
</div>

@if (session('status'))
    <div class="alert alert-success py-2 small" role="status">{{ session('status') }}</div>
@endif

<form method="POST" action="{{ route('login') }}" id="loginForm" class="needs-validation" novalidate>
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
        <label for="password" class="form-label">Password</label>
        <div class="input-group has-validation">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter your password" autocomplete="current-password" required>
            <button type="button" class="btn password-toggle" data-toggle-target="password" data-toggle-label="password" aria-label="Show password"><i class="bi bi-eye"></i></button>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div class="invalid-feedback">Please enter your password.</div>
            @enderror
        </div>
        <div class="hint" data-caps-for="password"><i class="bi bi-exclamation-triangle me-1"></i>Caps Lock is on</div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" value="1" @checked(old('remember'))>
            <label class="form-check-label" for="rememberMe">Remember me</label>
        </div>
        <a href="{{ route('password.request') }}" class="forgot-password" data-transition='Opening... <i class="bi bi-arrow-right ms-1"></i>'>Forgot Password?</a>
    </div>

    <button type="submit" class="btn btn-auth w-100" id="loginButton">
        <i class="bi bi-box-arrow-in-right me-2"></i>Login
    </button>
</form>

<div class="lock-banner" id="lockBanner" role="alert"></div>

@endsection

@push('scripts')
<script>
const email = $("email"), password = $("password"), rememberMe = $("rememberMe"),
      loginForm = $("loginForm"), loginButton = $("loginButton"), lockBanner = $("lockBanner");
const BTN_HTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Login';
const serverErrors = @json($errors->all());

/* ---------- Typewriter ---------- */
(function () {
    const lines = ["Manage every asset in one place.", "Track location, condition and value.", "Monitor maintenance in real time.", "Built for Majlis Daerah Perak Tengah."];
    const el = $("typer"); let i = 0, c = 0, del = false;
    if (reduced) { el.textContent = lines[0]; return; }
    (function tick() {
        const t = lines[i];
        el.textContent = t.slice(0, c);
        if (!del && c === t.length) { del = true; return setTimeout(tick, 1600); }
        if (del && c === 0) { del = false; i = (i + 1) % lines.length; }
        c += del ? -1 : 1;
        setTimeout(tick, del ? 25 : 55);
    })();
})();

/* ---------- Greeting + live clock ---------- */
function updateClock() {
    const d = new Date(), h = d.getHours();
    $("greeting").textContent = (h < 12 ? "Good morning" : h < 18 ? "Good afternoon" : "Good evening") + ", welcome back";
    $("clock").textContent = d.toLocaleDateString("en-GB", { weekday: "long", day: "numeric", month: "long", year: "numeric" }) +
        " • " + d.toLocaleTimeString("en-GB");
}
updateClock(); setInterval(updateClock, 1000);

/* ---------- Remember email (only fills when the server did not already repopulate it) ---------- */
try {
    const saved = localStorage.getItem("assetoneRememberedEmail");
    if (saved && !email.value) { email.value = saved; rememberMe.checked = true; }
} catch (e) {}
if (email.value && !serverErrors.length) email.classList.add("is-valid");
(email.value ? password : email).focus();

/* ---------- Live validation ---------- */
function mark(input, ok) {
    input.classList.toggle("is-valid", ok && input.value !== "");
    input.classList.toggle("is-invalid", !ok && input.value !== "");
}
email.addEventListener("input", () => mark(email, EMAIL_RE.test(email.value.trim())));
password.addEventListener("input", () => mark(password, password.value.length > 0));

/* ---------- Server-side errors: toast, shake and lockout countdown ---------- */
function startLock(seconds) {
    let s = seconds;
    loginButton.disabled = true; lockBanner.classList.add("show");
    const render = () => lockBanner.innerHTML = '<i class="bi bi-shield-exclamation me-2"></i>Too many failed attempts. Try again in <b>' + s + 's</b>.';
    render();
    const t = setInterval(() => {
        s--; render();
        if (s <= 0) { clearInterval(t); loginButton.disabled = false; lockBanner.classList.remove("show"); toast("You can try signing in again.", "info"); }
    }, 1000);
}
if (serverErrors.length) {
    shake();
    toast(serverErrors[0], "error");
    const m = serverErrors[0].match(/in (\d+) seconds/);
    if (m) startLock(parseInt(m[1], 10));
}
/* ---------- Submit ---------- */
function setProgress(p) { $("progressFill").style.width = p + "%"; }
loginForm.addEventListener("submit", e => {
    loginForm.classList.add("was-validated");
    const okEmail = EMAIL_RE.test(email.value.trim()), okPass = password.value.length > 0;
    mark(email, okEmail); mark(password, okPass);
    if (!okEmail || !okPass) {
        e.preventDefault();
        if (!email.value) email.classList.add("is-invalid");
        if (!password.value) password.classList.add("is-invalid");
        shake();
        toast("Please fix the highlighted fields.", "error");
        (okEmail ? password : email).focus();
        return;
    }
    try {
        rememberMe.checked ? localStorage.setItem("assetoneRememberedEmail", email.value.trim())
                           : localStorage.removeItem("assetoneRememberedEmail");
    } catch (err) {}

    loginButton.disabled = true;
    loginButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Verifying...';
    const ov = $("loginOverlay");
    ov.classList.add("active"); ov.setAttribute("aria-hidden", "false");
    setProgress(30); setTimeout(() => setProgress(65), 500); setTimeout(() => setProgress(88), 1400);
    /* the browser now submits the form; Laravel verifies and redirects */
});

/* Coming back via the browser's Back button: undo the loading state */
addEventListener("pageshow", e => {
    if (!e.persisted) return;
    const ov = $("loginOverlay");
    ov.classList.remove("active"); ov.setAttribute("aria-hidden", "true"); setProgress(0);
    loginButton.disabled = false; loginButton.innerHTML = BTN_HTML;
});
</script>
@endpush
