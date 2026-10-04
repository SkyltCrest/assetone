@extends('layouts.auth-card')

@section('title', 'Forgot Password')
@section('body-class', 'recovery')
@section('brand-tagline', 'Recover your account securely and regain access to AssetOne.')
@section('brand-icon', 'bi-shield-lock')

@push('styles')
<style>
#successSection{animation:rise .7s cubic-bezier(.22,1,.36,1)}
.ck{width:84px;height:84px;display:block;margin:0 auto 6px;filter:drop-shadow(0 10px 24px rgba(5,150,105,.5))}
.ck circle{fill:rgba(5,150,105,.25);stroke:#34d399;stroke-width:2.5;stroke-dasharray:151;stroke-dashoffset:151;transform-origin:center;transform:rotate(-90deg);animation:ckDraw .6s ease-out forwards}
.ck path{fill:none;stroke:#fff;stroke-width:4;stroke-linecap:round;stroke-linejoin:round;stroke-dasharray:40;stroke-dashoffset:40;animation:ckDraw .4s ease-out .45s forwards}
@keyframes ckDraw{to{stroke-dashoffset:0}}
.sent-to{text-align:center;font-size:14px;color:rgba(255,255,255,.75);margin:0 0 4px}
.sent-to b{color:#fff}
.continue-button{display:flex;align-items:center;justify-content:center;height:50px;border:1px solid rgba(255,255,255,.2);border-radius:12px;color:#fff;font-weight:600;text-decoration:none;background:rgba(25,135,84,.75);transition:all .3s}
.continue-button:hover{color:#fff;transform:translateY(-2px);background:rgba(25,135,84,.9)}
.resend-row{display:flex;justify-content:space-between;align-items:center;margin-top:14px;font-size:13px;color:rgba(255,255,255,.7)}
.link-btn{background:none;border:0;padding:0;color:#90caf9;font-weight:600;font-size:13px;transition:color .3s}
.link-btn:hover:not(:disabled){color:#bbdefb;text-decoration:underline}
.link-btn:disabled{color:rgba(255,255,255,.4);cursor:not-allowed}
.cd-bar{height:3px;border-radius:3px;background:rgba(255,255,255,.12);margin-top:8px;overflow:hidden}
.cd-bar i{display:block;height:100%;width:100%;background:linear-gradient(90deg,#90caf9,#34d399);transform-origin:left}
body.recovery .auth-footer{margin-top:25px}
</style>
@endpush

@section('content')
@php
    // The controller flashes the typed email (withInput) together with the status message.
    $sent = (bool) session('status');
    $sentTo = $sent ? old('email') : null;
    $masked = null;
    if ($sentTo && str_contains($sentTo, '@')) {
        [$user, $domain] = explode('@', $sentTo, 2);
        $masked = mb_substr($user, 0, 1).str_repeat('*', max(2, mb_strlen($user) - 1)).'@'.$domain;
    }
@endphp

<div class="auth-header">
    <h2>Forgot Password?</h2>
    <p>Enter your email address and we will help you reset your password.</p>
</div>

<div class="stepper" aria-label="Progress">
    <div class="st {{ $sent ? 'done' : 'on' }}" id="s0"><b>@if ($sent)<i class="bi bi-check-lg"></i>@else 1 @endif</b><span>Email</span></div><div class="ln {{ $sent ? 'fill' : '' }}" id="l0"><i></i></div>
    <div class="st {{ $sent ? 'on' : '' }}" id="s1"><b>2</b><span>Check inbox</span></div><div class="ln" id="l1"><i></i></div>
    <div class="st" id="s2"><b>3</b><span>Reset</span></div>
</div>

<form method="POST" action="{{ route('password.email') }}" id="forgotPasswordForm" class="needs-validation {{ $sent ? 'd-none' : '' }}" novalidate>
    @csrf
    <div class="mb-4">
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
    <button type="submit" id="resetButton" class="btn btn-auth w-100">
        <i class="bi bi-envelope-arrow-up me-2"></i>Send Reset Link
    </button>
</form>

@if ($sent)
<div id="successSection">
    <svg class="ck" viewBox="0 0 52 52" aria-hidden="true"><circle cx="26" cy="26" r="24"/><path d="M15 27l8 8 15-17"/></svg>
    @if ($masked)
        <p class="sent-to">Link sent to <b>{{ $masked }}</b></p>
    @endif
    <div class="alert success-message mt-3 mb-3" role="status">
        <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
    </div>
    <a href="{{ route('login') }}" class="continue-button w-100" data-transition='<i class="bi bi-arrow-left me-2"></i>Opening...'>
        <i class="bi bi-arrow-left me-2"></i>Back to Login
    </a>
    <div class="resend-row">
        @if ($sentTo)
            <span id="resendText">Didn't get it? Resend in <b id="cd">60</b>s</span>
            <span>
                <button type="submit" form="resendForm" class="link-btn" id="resendBtn" disabled>Resend link</button>
                &nbsp;•&nbsp;<button type="button" class="link-btn" id="changeEmail">Use another email</button>
            </span>
        @else
            <span></span>
            <span><button type="button" class="link-btn" id="changeEmail">Use another email</button></span>
        @endif
    </div>
    @if ($sentTo)
        <div class="cd-bar"><i id="cdBar"></i></div>
        <form method="POST" action="{{ route('password.email') }}" id="resendForm" class="d-none">
            @csrf
            <input type="hidden" name="email" value="{{ $sentTo }}">
        </form>
    @endif
</div>
@endif

<div class="text-center mt-4 {{ $sent ? 'd-none' : '' }}" id="backLoginWrap">
    <a href="{{ route('login') }}" class="back-login" data-transition='<i class="bi bi-arrow-left"></i> Opening...'><i class="bi bi-arrow-left"></i>Back to Login</a>
</div>

@endsection

@push('scripts')
<script>
const forgotPasswordForm = $("forgotPasswordForm"), email = $("email"), resetButton = $("resetButton"),
      successSection = $("successSection");
const BTN_HTML = '<i class="bi bi-envelope-arrow-up me-2"></i>Send Reset Link';
const COOLDOWN = 60;
const sent = @json($sent);
const serverErrors = @json($errors->all());

decodeTitle();

/* ---------- Staggered entrance ---------- */
if (!reduced) {
    [document.querySelector(".auth-header"), document.querySelector(".stepper"), sent ? successSection : forgotPasswordForm,
     $("backLoginWrap"), document.querySelector(".auth-footer")]
        .forEach((el, i) => el.style.animation = `rise .7s cubic-bezier(.22,1,.36,1) ${.5 + i * .1}s backwards`);
}

/* ---------- Stepper ---------- */
function setStep(n) {
    [0, 1, 2].forEach(i => { $("s" + i).className = "st" + (i < n ? " done" : i === n ? " on" : ""); });
    $("l0").classList.toggle("fill", n >= 1); $("l1").classList.toggle("fill", n >= 2);
    document.querySelectorAll(".st.done b").forEach(b => b.innerHTML = '<i class="bi bi-check-lg"></i>');
}

/* ---------- Prefill + live validation ---------- */
if (!sent) {
    try { const s = localStorage.getItem("assetoneRememberedEmail"); if (s && !email.value) { email.value = s; } } catch (e) {}
    if (email.value && !serverErrors.length) email.classList.add("is-valid");
    email.focus();
}
email.addEventListener("input", () => {
    const ok = EMAIL_RE.test(email.value.trim());
    email.classList.toggle("is-valid", ok && email.value !== "");
    email.classList.toggle("is-invalid", !ok && email.value !== "");
});

/* ---------- Server feedback ---------- */
if (serverErrors.length) { shake(); toast(serverErrors[0], "error"); }
if (sent) toast("Reset link sent. Check your inbox.", "success");

/* ---------- Resend cooldown ---------- */
let cdTimer;
function startCooldown() {
    const btn = $("resendBtn"), bar = $("cdBar");
    if (!btn || !bar) return;
    let s = COOLDOWN;
    btn.disabled = true; $("resendText").style.visibility = "visible";
    bar.style.transition = "none"; bar.style.transform = "scaleX(1)"; void bar.offsetWidth;
    bar.style.transition = `transform ${COOLDOWN}s linear`; bar.style.transform = "scaleX(0)";
    $("cd").textContent = s;
    cdTimer = setInterval(() => {
        s--; $("cd").textContent = s;
        if (s <= 0) { clearInterval(cdTimer); btn.disabled = false; $("resendText").style.visibility = "hidden"; }
    }, 1000);
}
if (sent) startCooldown();

const changeEmail = $("changeEmail");
if (changeEmail) changeEmail.addEventListener("click", () => {
    clearInterval(cdTimer);
    successSection.classList.add("d-none"); forgotPasswordForm.classList.remove("d-none");
    $("backLoginWrap").classList.remove("d-none");
    forgotPasswordForm.classList.remove("was-validated");
    resetButton.disabled = false; resetButton.innerHTML = BTN_HTML;
    setStep(0); email.select();
});

/* ---------- Submit ---------- */
forgotPasswordForm.addEventListener("submit", e => {
    forgotPasswordForm.classList.add("was-validated");
    if (!EMAIL_RE.test(email.value.trim())) {
        e.preventDefault();
        email.classList.add("is-invalid");
        shake(); toast("Please enter a valid email address.", "error"); email.focus();
        return;
    }
    resetButton.disabled = true;
    resetButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending Reset Link...';
    /* the browser now submits the form; Laravel sends the email and redirects back here */
});

/* Back button: undo the loading state */
addEventListener("pageshow", e => {
    if (e.persisted && resetButton) { resetButton.disabled = false; resetButton.innerHTML = BTN_HTML; }
});
</script>
@endpush
