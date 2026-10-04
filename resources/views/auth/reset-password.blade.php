@extends('layouts.auth-card')

@section('title', 'Reset Password')
@section('body-class', 'recovery')
@section('brand-tagline', 'Create a new secure password to protect your AssetOne account.')
@section('brand-icon', 'bi-shield-lock')

@push('styles')
<style>
/* Slightly tighter than the other pages so the longer form fits */
.auth-section{padding:40px 60px}
body.recovery .auth-header{margin-bottom:18px}
.stepper{margin-bottom:20px}
.form-control{height:48px}
.btn-auth{height:50px}
.auth-footer{margin-top:20px}
.footer-logo{margin-bottom:8px}
.strength-bar-wrapper{height:6px;border-radius:4px;background:rgba(255,255,255,.12);margin-top:8px;overflow:hidden}
.strength-bar{height:100%;width:0;transition:width .35s cubic-bezier(.34,1.56,.64,1),background-color .25s}
.strength-label{font-size:12px;font-weight:600;margin-top:3px;min-height:18px}
.password-requirements{font-size:12px;margin-top:6px}
.password-requirements p{margin-bottom:4px;color:rgba(255,255,255,.75);font-weight:600}
.requirement{display:flex;align-items:center;gap:6px;margin-bottom:2px;color:rgba(255,255,255,.6);transition:color .3s,transform .3s}
.requirement i{font-size:12px}
.requirement.valid{color:#a7f3d0;font-weight:600;transform:translateX(4px)}
.requirement.valid i{animation:iconPop .5s ease}
.gen-row{display:flex;gap:8px;margin-top:10px}
.btn-soft{flex:1;height:38px;border-radius:10px;font-size:13px;font-weight:600;color:#bbdefb;background:rgba(255,255,255,.08);border:1px dashed rgba(144,202,249,.5);transition:all .3s}
.btn-soft:hover{color:#fff;background:rgba(33,150,243,.25);border-style:solid}
@media(max-width:767px){.auth-section{padding:36px 25px}}
</style>
@endpush

@section('content')

<div class="auth-header">
    <h2>Reset Password</h2>
    <p>Create a new password for your AssetOne account.</p>
</div>

<div class="stepper" aria-label="Progress">
    <div class="st done" id="s0"><b><i class="bi bi-check-lg"></i></b><span>Email</span></div><div class="ln fill" id="l0"><i></i></div>
    <div class="st done" id="s1"><b><i class="bi bi-check-lg"></i></b><span>Check inbox</span></div><div class="ln fill" id="l1"><i></i></div>
    <div class="st on" id="s2"><b>3</b><span>Reset</span></div>
</div>

@error('email')
    <div class="alert alert-danger py-2 small" role="alert">
        {{ $message }}
        <a href="{{ route('password.request') }}">Request a new reset link</a>
    </div>
@enderror

<form method="POST" action="{{ route('password.store') }}" id="resetPasswordForm" class="needs-validation" novalidate>
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ old('email', $email) }}">

    <div class="mb-3">
        <label for="password" class="form-label">New Password</label>
        <div class="input-group has-validation">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter your new password" autocomplete="new-password" minlength="8" required>
            <button type="button" class="btn password-toggle" data-toggle-target="password" data-toggle-label="new password" aria-label="Show new password"><i class="bi bi-eye"></i></button>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div class="invalid-feedback">Password must be at least 8 characters.</div>
            @enderror
        </div>
        <div class="hint" data-caps-for="password"><i class="bi bi-exclamation-triangle me-1"></i>Caps Lock is on</div>

        <div class="strength-bar-wrapper"><div class="strength-bar" id="strengthBar"></div></div>
        <div class="strength-label" id="strengthLabel"></div>

        <div class="password-requirements">
            <p>Password requirements:</p>
            <div class="requirement" id="lengthRequirement"><i class="bi bi-circle"></i>At least 8 characters</div>
            <div class="requirement" id="uppercaseRequirement"><i class="bi bi-circle"></i>At least one uppercase letter</div>
            <div class="requirement" id="lowercaseRequirement"><i class="bi bi-circle"></i>At least one lowercase letter</div>
            <div class="requirement" id="numberRequirement"><i class="bi bi-circle"></i>At least one number</div>
            <div class="requirement" id="specialRequirement"><i class="bi bi-circle"></i>At least one special character</div>
        </div>

        <div class="gen-row">
            <button type="button" class="btn btn-soft" id="genBtn"><i class="bi bi-magic me-1"></i>Generate strong password</button>
            <button type="button" class="btn btn-soft" id="copyBtn" style="flex:0 0 auto;padding:0 14px" aria-label="Copy password"><i class="bi bi-clipboard"></i></button>
        </div>
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirm New Password</label>
        <div class="input-group has-validation">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirm your new password" autocomplete="new-password" required>
            <button type="button" class="btn password-toggle" data-toggle-target="password_confirmation" data-toggle-label="confirm password" aria-label="Show confirm password"><i class="bi bi-eye"></i></button>
            <div class="invalid-feedback" id="confirmPasswordFeedback">Please confirm your password.</div>
        </div>
        <div class="hint" data-caps-for="password_confirmation"><i class="bi bi-exclamation-triangle me-1"></i>Caps Lock is on</div>
    </div>

    <button type="submit" id="resetButton" class="btn btn-auth w-100"><i class="bi bi-key me-2"></i>Reset Password</button>
</form>

<div class="text-center mt-3">
    <a href="{{ route('login') }}" class="back-login" data-transition='<i class="bi bi-arrow-left"></i> Opening...'><i class="bi bi-arrow-left"></i>Back to Login</a>
</div>

@endsection

@push('scripts')
<script>
const resetPasswordForm = $("resetPasswordForm"), newPassword = $("password"), confirmPassword = $("password_confirmation"),
      confirmPasswordFeedback = $("confirmPasswordFeedback"), resetButton = $("resetButton"),
      strengthBar = $("strengthBar"), strengthLabel = $("strengthLabel");
const SPECIAL = /[!@#$%^&*(),.?":{}|<>_\-+=~`;'\[\]\/\\]/;
const BTN_HTML = '<i class="bi bi-key me-2"></i>Reset Password';
const serverErrors = @json($errors->all());

decodeTitle();

/* ---------- Staggered entrance ---------- */
if (!reduced) {
    [document.querySelector(".auth-header"), document.querySelector(".stepper")]
        .concat([...resetPasswordForm.children].filter(el => el.type !== "hidden"), [document.querySelector(".text-center"), document.querySelector(".auth-footer")])
        .forEach((el, i) => el.style.animation = `rise .7s cubic-bezier(.22,1,.36,1) ${.45 + i * .08}s backwards`);
}

/* ---------- Requirements + strength meter ---------- */
function updateRequirement(element, isValid) {
    element.classList.toggle("valid", isValid);
    element.querySelector("i").className = isValid ? "bi bi-check-circle-fill" : "bi bi-circle";
}
function updateStrengthMeter(checks, password) {
    const count = Object.values(checks).filter(Boolean).length;
    if (password.length === 0) { strengthBar.style.width = "0%"; strengthLabel.textContent = ""; return; }
    const lv = count <= 2 ? ["25%", "#dc3545", "Weak", "#ffcdd2"] : count === 3 ? ["50%", "#fd7e14", "Fair", "#ffccbc"]
             : count === 4 ? ["80%", "#ffc107", "Good", "#ffe082"] : ["100%", "#198754", "Strong", "#a7f3d0"];
    strengthBar.style.width = lv[0]; strengthBar.style.backgroundColor = lv[1];
    strengthLabel.textContent = lv[2]; strengthLabel.style.color = lv[3];
}

/* ---------- Password match ---------- */
function checkPasswordMatch() {
    if (confirmPassword.value === "") { confirmPassword.classList.remove("is-invalid", "is-valid"); return; }
    const matches = newPassword.value === confirmPassword.value;
    confirmPassword.classList.toggle("is-invalid", !matches);
    confirmPassword.classList.toggle("is-valid", matches);
    confirmPasswordFeedback.textContent = matches ? "" : "Passwords do not match.";
}

newPassword.addEventListener("input", () => {
    const p = newPassword.value;
    const checks = { length: p.length >= 8, upper: /[A-Z]/.test(p), lower: /[a-z]/.test(p), number: /[0-9]/.test(p), special: SPECIAL.test(p) };
    updateRequirement($("lengthRequirement"), checks.length);
    updateRequirement($("uppercaseRequirement"), checks.upper);
    updateRequirement($("lowercaseRequirement"), checks.lower);
    updateRequirement($("numberRequirement"), checks.number);
    updateRequirement($("specialRequirement"), checks.special);
    updateStrengthMeter(checks, p);
    if (checks.length) newPassword.classList.remove("is-invalid");
    newPassword.classList.toggle("is-valid", Object.values(checks).every(Boolean));
    checkPasswordMatch();
});
confirmPassword.addEventListener("input", checkPasswordMatch);

/* ---------- Strong password generator + copy ---------- */
function genPass() {
    const U = "ABCDEFGHJKLMNPQRSTUVWXYZ", L = "abcdefghijkmnopqrstuvwxyz", N = "23456789", S = "!@#$%^&*-_+=?", all = U + L + N + S;
    const r = n => crypto.getRandomValues(new Uint32Array(1))[0] % n;
    const a = [U[r(U.length)], L[r(L.length)], N[r(N.length)], S[r(S.length)]];
    while (a.length < 16) a.push(all[r(all.length)]);
    for (let i = a.length - 1; i > 0; i--) { const j = r(i + 1); [a[i], a[j]] = [a[j], a[i]]; }
    return a.join("");
}
$("genBtn").addEventListener("click", () => {
    const p = genPass();
    newPassword.value = confirmPassword.value = p;
    document.querySelectorAll(".password-toggle").forEach(btn => {
        $(btn.dataset.toggleTarget).type = "text"; btn.querySelector("i").className = "bi bi-eye-slash";
    });
    newPassword.dispatchEvent(new Event("input"));
    toast("Strong password generated. Copy it before you continue.", "success");
});
$("copyBtn").addEventListener("click", async () => {
    if (!newPassword.value) return toast("Nothing to copy yet.", "info");
    try { await navigator.clipboard.writeText(newPassword.value); toast("Password copied to clipboard.", "success"); }
    catch (e) { toast("Copy failed. Select the text and copy manually.", "error"); }
});

/* ---------- Server feedback ---------- */
if (serverErrors.length) { shake(); toast(serverErrors[0], "error"); }

/* ---------- Submit ---------- */
resetPasswordForm.addEventListener("submit", e => {
    resetPasswordForm.classList.add("was-validated");

    const fail = msg => { e.preventDefault(); shake(); toast(msg, "error"); };

    if (!resetPasswordForm.checkValidity()) return fail("Please complete all fields.");

    if (newPassword.value !== confirmPassword.value) {
        confirmPassword.classList.add("is-invalid");
        confirmPasswordFeedback.textContent = "Passwords do not match.";
        return fail("Passwords do not match.");
    }

    const v = newPassword.value;
    if (!(v.length >= 8 && /[A-Z]/.test(v) && /[a-z]/.test(v) && /[0-9]/.test(v) && SPECIAL.test(v))) {
        newPassword.classList.add("is-invalid");
        return fail("Password does not meet all requirements.");
    }

    resetButton.disabled = true;
    resetButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Resetting Password...';
    /* the browser now submits the form; Laravel saves the password and redirects to the login page */
});

/* Back button: undo the loading state */
addEventListener("pageshow", e => {
    if (e.persisted) { resetButton.disabled = false; resetButton.innerHTML = BTN_HTML; }
});

newPassword.focus();
</script>
@endpush
