<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title') | AssetOne</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

<style>
*{box-sizing:border-box}
html,body{margin:0;padding:0}
body{min-height:100vh;font-family:"Plus Jakarta Sans","Segoe UI",Arial,sans-serif;color:#fff;background:linear-gradient(135deg,#06111f 0%,#0b2038 45%,#0d47a1 100%) fixed;overflow-x:hidden}

/* Background */
.aurora-bg{position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden}
.blob{position:absolute;border-radius:50%;filter:blur(80px);opacity:.55;transition:transform .8s cubic-bezier(.2,.8,.2,1)}
.b1{width:520px;height:520px;top:-180px;left:-180px;background:radial-gradient(circle,rgba(33,150,243,.9),transparent 70%)}
.b2{width:480px;height:480px;top:40%;right:-160px;background:radial-gradient(circle,rgba(103,58,183,.85),transparent 70%)}
.b3{width:460px;height:460px;bottom:-180px;left:30%;background:radial-gradient(circle,rgba(0,188,212,.8),transparent 70%)}
.cursor-glow{position:fixed;width:420px;height:420px;border-radius:50%;left:0;top:0;z-index:2;pointer-events:none;background:radial-gradient(circle,rgba(144,202,249,.18),transparent 65%);transform:translate(-50%,-50%)}

/* Card */
.auth-wrapper{min-height:100vh;display:flex;justify-content:center;align-items:center;padding:40px 20px;position:relative;z-index:5}
.auth-card{width:100%;max-width:1050px;min-height:620px;position:relative;overflow:hidden;display:flex;border-radius:30px;background:rgba(255,255,255,.10);backdrop-filter:blur(30px) saturate(160%);-webkit-backdrop-filter:blur(30px) saturate(160%);border:1px solid rgba(255,255,255,.28);box-shadow:0 35px 90px rgba(0,0,0,.45),inset 0 1px 0 rgba(255,255,255,.35);animation:cardIn .9s cubic-bezier(.22,1,.36,1);transition:transform .25s ease-out,box-shadow .4s}
.auth-card:hover{box-shadow:0 45px 100px rgba(0,0,0,.5),0 0 60px rgba(33,150,243,.15),inset 0 1px 0 rgba(255,255,255,.4)}
.auth-card::before{content:"";position:absolute;inset:0;z-index:3;pointer-events:none;background:radial-gradient(500px circle at var(--mx,50%) var(--my,0%),rgba(255,255,255,.14),transparent 45%)}
@property --ang{syntax:"<angle>";initial-value:0deg;inherits:false}
.auth-card::after{content:"";position:absolute;inset:-1px;border-radius:30px;padding:1px;pointer-events:none;background:conic-gradient(from var(--ang),transparent 0 68%,rgba(144,202,249,.95) 84%,rgba(52,211,153,.9) 92%,transparent 100%);-webkit-mask:linear-gradient(#fff 0 0) content-box,linear-gradient(#fff 0 0);-webkit-mask-composite:xor;mask-composite:exclude;animation:angspin 6s linear infinite}
@keyframes angspin{to{--ang:360deg}}
@keyframes cardIn{from{opacity:0;transform:translateY(40px) scale(.96)}to{opacity:1;transform:none}}
.auth-card>.row{margin:0;width:100%;min-height:inherit}
.auth-card>.row>[class*="col-"]{display:flex}

/* Brand */
.brand-section{width:100%;padding:50px 45px;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;background:linear-gradient(135deg,rgba(21,101,192,.55),rgba(13,71,161,.35));border-right:1px solid rgba(255,255,255,.2);position:relative;overflow:hidden}
.brand-section::before,.brand-section::after{content:"";position:absolute;width:200px;height:200px;border-radius:50%;filter:blur(30px);pointer-events:none}
.brand-section::before{top:20%;left:-50px;background:radial-gradient(circle,rgba(144,202,249,.3),transparent 70%);animation:orb 8s ease-in-out infinite}
.brand-section::after{bottom:10%;right:-50px;background:radial-gradient(circle,rgba(103,58,183,.3),transparent 70%);animation:orb 10s ease-in-out infinite reverse}
@keyframes orb{50%{transform:translate(30px,-30px) scale(1.2)}}
.brand-logo{width:135px;max-width:70%;margin-bottom:25px;filter:drop-shadow(0 8px 15px rgba(0,0,0,.35));animation:float 4s ease-in-out infinite;position:relative;z-index:2}
@keyframes float{50%{transform:translateY(-8px) rotate(-2deg)}}
.brand-title{font-size:34px;font-weight:800;margin-bottom:10px;background:linear-gradient(90deg,#fff,#90caf9 25%,#fff 50%,#bbdefb 75%,#fff);background-size:200% auto;-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;animation:grad 4s linear infinite;position:relative;z-index:2}
@keyframes grad{to{background-position:200% center}}
.brand-subtitle{font-size:16px;line-height:1.6;max-width:360px;opacity:.92;position:relative;z-index:2}
.brand-icon{font-size:58px;margin-top:22px;opacity:.85;filter:drop-shadow(0 5px 10px rgba(0,0,0,.2));animation:iconFloat 3s ease-in-out infinite;position:relative;z-index:2}
@keyframes iconFloat{50%{transform:translateY(-8px)}}
.stats{display:flex;gap:10px;margin-top:22px;position:relative;z-index:2;flex-wrap:wrap;justify-content:center}
.stat{padding:8px 14px;border-radius:12px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);font-size:12px;transition:transform .3s cubic-bezier(.34,1.56,.64,1),background .3s}
.stat:hover{transform:translateY(-4px) scale(1.06);background:rgba(255,255,255,.2)}
.stat i{color:#90caf9;margin-right:5px}

/* Form section */
.auth-section{width:100%;padding:55px 60px;display:flex;flex-direction:column;justify-content:center;background:rgba(255,255,255,.07);animation:secIn 1s ease-out .2s both}
@keyframes secIn{from{opacity:0;transform:translateX(30px)}to{opacity:1;transform:none}}
.auth-header{margin-bottom:28px}
.auth-header h2{font-size:29px;font-weight:700;margin-bottom:8px}
.auth-header p{color:rgba(255,255,255,.72);margin:0;line-height:1.6}
.clock{font-size:12px;color:#90caf9;font-weight:600;margin-bottom:6px}
.form-label{font-weight:600;color:rgba(255,255,255,.92);font-size:14px}
.input-group-text{width:48px;justify-content:center;color:#fff;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-right:none;transition:all .3s}
.form-control{height:50px;color:#fff;background-color:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);transition:all .3s;font-weight:500}
.form-control::placeholder{color:rgba(255,255,255,.55);font-weight:400}
.form-control:hover{background-color:rgba(255,255,255,.15);border-color:rgba(255,255,255,.35)}
.form-control:focus{color:#fff;background-color:rgba(255,255,255,.18);border-color:#90caf9;box-shadow:0 0 0 .2rem rgba(33,150,243,.2),0 0 30px rgba(33,150,243,.25)}
.form-control.is-valid{border-color:#34d399}
.form-control.is-invalid{border-color:#ff8a80}
.input-group:focus-within .input-group-text{color:#90caf9;background:rgba(33,150,243,.18);border-color:rgba(144,202,249,.5)}
.password-toggle{width:50px;color:rgba(255,255,255,.85);background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);border-left:none}
.password-toggle:hover{color:#fff;background:rgba(255,255,255,.2)}
.password-toggle:focus{box-shadow:none}
.invalid-feedback{color:#ffcdd2}
.hint{font-size:12px;margin-top:6px;color:#ffe082;display:none}
.hint.show{display:block}
.form-check-label{color:rgba(255,255,255,.75);font-size:14px}
.form-check-input{background-color:rgba(255,255,255,.1);border-color:rgba(255,255,255,.35)}
.form-check-input:checked{background-color:#1565c0;border-color:#1565c0}
.back-login{color:#90caf9;text-decoration:none;font-size:14px;font-weight:600;position:relative}
.back-login:hover{color:#bbdefb}
.back-login::after{content:"";position:absolute;left:0;bottom:-4px;width:0;height:2px;background:#90caf9;transition:width .3s}
.back-login:hover::after{width:100%}
.btn-auth{height:52px;border:none;border-radius:12px;color:#fff;font-weight:600;font-size:15px;position:relative;overflow:hidden;background:linear-gradient(135deg,#1976d2,#0d47a1);box-shadow:0 8px 25px rgba(13,71,161,.35),inset 0 1px 0 rgba(255,255,255,.2);transition:box-shadow .3s,transform .15s}
.btn-auth::before{content:"";position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.25),transparent);transition:left .6s ease}
.btn-auth:hover{color:#fff;box-shadow:0 12px 30px rgba(13,71,161,.45),0 0 40px rgba(33,150,243,.25)}
.btn-auth:hover::before{left:100%}
.btn-auth:disabled{opacity:.75;cursor:not-allowed}

.alert{border-radius:12px;backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.2)}
.alert-success{background:rgba(25,135,84,.2);color:#d1fae5}
.alert-warning{background:rgba(251,191,36,.16);color:#fef3c7;border-color:rgba(251,191,36,.4)}
.alert-danger{background:rgba(244,67,54,.18);color:#ffcdd2;border-color:rgba(255,138,128,.4)}

.password-requirements{font-size:13px;margin-top:12px}
.password-requirements p{margin-bottom:5px;color:rgba(255,255,255,.7)}
.requirement{display:flex;align-items:center;gap:6px;margin-bottom:3px;color:rgba(255,255,255,.6);transition:color .25s}
.requirement i{font-size:13px}
.requirement.valid{color:#6ee7b7}

.auth-footer{margin-top:28px;text-align:center;font-size:13px;color:rgba(255,255,255,.6)}
.footer-logo{height:34px;margin-bottom:10px;opacity:.85}

@media(max-width:767px){
    .auth-wrapper{padding:20px 15px}
    .auth-card{min-height:auto;border-radius:22px;display:block}
    .brand-section{padding:36px 25px;border-right:none;border-bottom:1px solid rgba(255,255,255,.2)}
    .brand-logo{width:105px}.brand-title{font-size:26px}.brand-subtitle{font-size:14px}
    .stats,.brand-icon{display:none}
    .auth-section{padding:36px 25px}
    .cursor-glow{display:none}
}
@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important}}

/* ---- Ported from the prototype: particles, tilt, toasts, overlay, stepper ---- */
body{transition:opacity .6s ease}
body.page-exit{opacity:0}
#net{position:fixed;inset:0;pointer-events:none;z-index:1}
.auth-wrapper{perspective:1400px}
.auth-card{will-change:transform}
.auth-card.shake{animation:shakeX .45s}
@keyframes shakeX{20%{transform:translateX(-12px)}40%{transform:translateX(10px)}60%{transform:translateX(-8px)}80%{transform:translateX(6px)}}
.brand-logo{transition:transform .4s}
.brand-logo:hover{transform:scale(1.1) rotate(3deg);animation-play-state:paused}
.typer{min-height:52px;font-size:15px;color:#bbdefb;position:relative;z-index:2}
.typer::after{content:"";display:inline-block;width:2px;height:1em;margin-left:3px;vertical-align:-2px;background:#90caf9;animation:blink 1s steps(1) infinite}
@keyframes blink{50%{opacity:0}}
.form-control.is-valid{animation:okPulse .7s ease-out}
@keyframes okPulse{0%{box-shadow:0 0 0 0 rgba(52,211,153,.55)}100%{box-shadow:0 0 0 12px rgba(52,211,153,0)}}
@keyframes iconPop{0%{transform:scale(1)}40%{transform:scale(1.35) rotate(-10deg)}100%{transform:scale(1)}}
.input-group:focus-within .input-group-text i{animation:iconPop .5s ease}
.password-toggle i{display:inline-block;transition:transform .3s cubic-bezier(.34,1.56,.64,1)}
.password-toggle:active i{transform:scale(.6) rotate(-25deg)}
.btn-auth{background-size:200% 200%;transition:box-shadow .3s,background-position .4s,transform .15s}
.btn-auth:hover{background-position:100% 50%}
.btn-auth .ripple,.continue-button .ripple{position:absolute;border-radius:50%;background:rgba(255,255,255,.5);transform:scale(0);animation:rip .6s ease-out;pointer-events:none}
@keyframes rip{to{transform:scale(4);opacity:0}}
.back-login{transition:all .3s}
.back-login.clicked{transform:translateX(5px);opacity:.6}
.lock-banner{display:none;margin-top:14px;padding:10px 14px;border-radius:12px;font-size:14px;background:rgba(244,67,54,.18);border:1px solid rgba(255,138,128,.4);color:#ffcdd2}
.lock-banner.show{display:block}
.toasts{position:fixed;top:20px;right:20px;z-index:10000;display:flex;flex-direction:column;gap:10px}
.toast-i{min-width:260px;max-width:340px;padding:12px 16px;border-radius:14px;font-size:14px;display:flex;gap:10px;align-items:center;background:rgba(10,25,45,.85);backdrop-filter:blur(14px);border:1px solid rgba(255,255,255,.2);box-shadow:0 12px 30px rgba(0,0,0,.35);animation:tIn .4s cubic-bezier(.34,1.56,.64,1)}
.toast-i.error i{color:#ff8a80}.toast-i.success i{color:#34d399}.toast-i.info i{color:#90caf9}.toast-i.out{opacity:0;transform:translateX(30px);transition:.3s}
@keyframes tIn{from{opacity:0;transform:translateX(40px)}}
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
.stepper{display:flex;align-items:center;margin-bottom:24px}
.st{display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;color:rgba(255,255,255,.5);transition:color .4s}
.st b{width:26px;height:26px;border-radius:50%;display:grid;place-items:center;font-size:12px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.25);transition:all .4s cubic-bezier(.34,1.56,.64,1)}
.st.on{color:#fff}
.st.on b{background:#1976d2;border-color:#90caf9;box-shadow:0 0 18px rgba(33,150,243,.6);transform:scale(1.12)}
.st.done{color:#a7f3d0}
.st.done b{background:#059669;border-color:#34d399}
.ln{flex:1;height:2px;margin:0 10px;background:rgba(255,255,255,.15);position:relative;overflow:hidden;border-radius:2px}
.ln i{position:absolute;inset:0;background:linear-gradient(90deg,#90caf9,#34d399);transform:scaleX(0);transform-origin:left;transition:transform .6s ease}
.ln.fill i{transform:scaleX(1)}
.ck{width:86px;height:86px;filter:drop-shadow(0 10px 24px rgba(5,150,105,.5))}
.ck circle{fill:rgba(5,150,105,.25);stroke:#34d399;stroke-width:2.5;stroke-dasharray:151;stroke-dashoffset:151;transform-origin:center;transform:rotate(-90deg);animation:ckDraw .6s ease-out forwards}
.ck path{fill:none;stroke:#fff;stroke-width:4;stroke-linecap:round;stroke-linejoin:round;stroke-dasharray:40;stroke-dashoffset:40;animation:ckDraw .4s ease-out .45s forwards}
@keyframes ckDraw{to{stroke-dashoffset:0}}
.sent-to{text-align:center;font-size:14px;color:rgba(255,255,255,.75);margin:0 0 4px}
.sent-to b{color:#fff}
.resend-row{display:flex;justify-content:space-between;align-items:center;margin-top:14px;font-size:13px;color:rgba(255,255,255,.7);flex-wrap:wrap;gap:6px}
.link-btn{background:none;border:0;padding:0;color:#90caf9;font-weight:600;font-size:13px;transition:color .3s}
.link-btn:hover:not(:disabled){color:#bbdefb;text-decoration:underline}
.link-btn:disabled{color:rgba(255,255,255,.4);cursor:not-allowed}
.cd-bar{height:3px;border-radius:3px;background:rgba(255,255,255,.12);margin-top:8px;overflow:hidden}
.cd-bar i{display:block;height:100%;width:100%;background:linear-gradient(90deg,#90caf9,#34d399);transform-origin:left}
.gen-row{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap}
.gen-row .btn{font-size:13px;color:#fff;border:1px solid rgba(255,255,255,.25);background:rgba(255,255,255,.1);border-radius:10px}
.gen-row .btn:hover{background:rgba(255,255,255,.2);color:#fff}
.strength-bar-wrapper{height:6px;border-radius:99px;background:rgba(255,255,255,.14);overflow:hidden;margin-top:10px}
.strength-bar{height:100%;width:0;border-radius:99px;transition:width .3s,background .3s}
.strength-label{font-size:12px;font-weight:700;margin-top:5px;min-height:1.1em}
.cf{position:fixed;top:45%;left:50%;width:8px;height:12px;z-index:9999;pointer-events:none;border-radius:2px;animation:cf 1.5s cubic-bezier(.2,.8,.3,1) forwards}
@keyframes cf{to{transform:translate(var(--x),var(--y)) rotate(var(--r));opacity:0}}
@media(max-width:767px){.stats{display:none}}
</style>
@stack('styles')
</head>
<body>

<div class="aurora-bg" aria-hidden="true">
    <div class="blob b1" data-d="30"></div><div class="blob b2" data-d="-45"></div><div class="blob b3" data-d="60"></div>
</div>
<canvas id="net" aria-hidden="true"></canvas>
<div class="cursor-glow" id="cursorGlow" aria-hidden="true"></div>
<div class="toasts" id="toasts" aria-live="polite"></div>
<div id="authData" hidden data-errors="{{ json_encode($errors->all()) }}" data-status="{{ session('status') }}"></div>
@yield('overlay')

<div class="auth-wrapper">
    <div class="auth-card" id="card">
        <div class="row g-0 align-items-stretch">

            <div class="col-md-5">
                <div class="brand-section">
                    <img src="{{ asset('images/assetone-logo.png') }}" alt="AssetOne Logo" class="brand-logo">
                    <h1 class="brand-title">AssetOne</h1>
                    <p class="brand-subtitle">MDPT Asset Management System</p>
                    <p class="brand-subtitle">@yield('brand-tagline')</p>
                    @yield('brand-extra')
                    <i class="bi @yield('brand-icon') brand-icon"></i>
                    <div class="stats">
                        <span class="stat"><i class="bi bi-shield-lock"></i>Secure</span>
                        <span class="stat"><i class="bi bi-lightning-charge"></i>Real-time</span>
                        <span class="stat"><i class="bi bi-diagram-3"></i>Centralised</span>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="auth-section">
                    @if (session('error'))
                        <div class="alert alert-warning py-2 small">{{ session('error') }}</div>
                    @endif

                    @yield('content')

                    <div class="auth-footer">
                        <img src="{{ asset('images/mdpt-logo.png') }}" alt="MDPT Logo" class="footer-logo">
                        <p class="mb-1">&copy; {{ now()->year }} Majlis Daerah Perak Tengah</p>
                        <p class="mb-0">AssetOne Asset Management System</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    var reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.querySelectorAll('.password-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var field = document.getElementById(btn.dataset.toggleTarget);
            var icon = btn.querySelector('i');
            var isPassword = field.type === 'password';
            field.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
            btn.setAttribute('aria-label', (isPassword ? 'Hide ' : 'Show ') + (btn.dataset.toggleLabel || 'password'));
        });
    });

    // Caps Lock hint on password fields.
    document.querySelectorAll('input[type=password]').forEach(function (input) {
        var hint = document.querySelector('[data-caps-for="' + input.id + '"]');
        if (!hint) return;
        ['keydown', 'keyup'].forEach(function (ev) {
            input.addEventListener(ev, function (e) { if (e.getModifierState) hint.classList.toggle('show', e.getModifierState('CapsLock')); });
        });
        input.addEventListener('blur', function () { hint.classList.remove('show'); });
    });

    // Clock + time-of-day greeting.
    var clock = document.getElementById('clock'), greeting = document.getElementById('greeting');
    function tick() {
        var d = new Date(), h = d.getHours();
        if (clock) clock.textContent = d.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) + ' • ' + d.toLocaleTimeString('en-GB');
        if (greeting && greeting.dataset.greet !== undefined) greeting.textContent = (h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening') + ', welcome back';
    }
    tick(); setInterval(tick, 1000);

    // Card spotlight, cursor glow and background parallax.
    var card = document.getElementById('card'), glow = document.getElementById('cursorGlow');
    document.addEventListener('pointermove', function (e) {
        var r = card.getBoundingClientRect();
        card.style.setProperty('--mx', (e.clientX - r.left) + 'px');
        card.style.setProperty('--my', (e.clientY - r.top) + 'px');
        if (reduced) return;
        glow.style.transform = 'translate(' + e.clientX + 'px,' + e.clientY + 'px) translate(-50%,-50%)';
        var nx = e.clientX / innerWidth - .5, ny = e.clientY / innerHeight - .5;
        document.querySelectorAll('.blob').forEach(function (b) { b.style.transform = 'translate(' + (nx * b.dataset.d * 3) + 'px,' + (ny * b.dataset.d * 3) + 'px)'; });
    }, { passive: true });

    // Show a spinner on submit so double-clicks don't resend.
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            var btn = form.querySelector('.btn-auth');
            if (!btn || form.dataset.ownSubmit !== undefined) return;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Please wait...';
        });
    });
})();
</script>
<script>
/* Particle network, 3D tilt, ripple, toasts, page-exit links and server messages. */
(function () {
    var reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
    var card = document.getElementById('card');

    /* ---------- Toasts ---------- */
    window.authToast = function (msg, type) {
        type = type || 'info';
        var icons = { error: 'bi-x-octagon-fill', success: 'bi-check-circle-fill', info: 'bi-info-circle-fill' };
        var el = document.createElement('div'), i = document.createElement('i'), s = document.createElement('span');
        el.className = 'toast-i ' + type; i.className = 'bi ' + icons[type]; s.textContent = msg;
        el.appendChild(i); el.appendChild(s);
        document.getElementById('toasts').appendChild(el);
        setTimeout(function () { el.classList.add('out'); setTimeout(function () { el.remove(); }, 300); }, 4200);
    };
    window.authShake = function () { card.classList.remove('shake'); void card.offsetWidth; card.classList.add('shake'); };

    /* ---------- Interactive particle network ---------- */
    (function () {
        var cv = document.getElementById('net'), ctx = cv.getContext('2d'), W, H, pts = [], mouse = { x: -999, y: -999 };
        function resize() {
            W = cv.width = innerWidth; H = cv.height = innerHeight;
            var n = Math.min(90, Math.floor(W * H / 16000)); pts = [];
            for (var i = 0; i < n; i++) pts.push({ x: Math.random() * W, y: Math.random() * H, vx: (Math.random() - .5) * .5, vy: (Math.random() - .5) * .5, r: Math.random() * 1.8 + .8 });
        }
        addEventListener('resize', resize); resize();
        addEventListener('mousemove', function (e) { mouse.x = e.clientX; mouse.y = e.clientY; });
        document.addEventListener('mouseleave', function () { mouse.x = mouse.y = -999; });
        addEventListener('click', function (e) {
            pts.forEach(function (p) {
                var dx = p.x - e.clientX, dy = p.y - e.clientY, d = Math.hypot(dx, dy) || 1;
                if (d < 220) { p.vx += dx / d * 3; p.vy += dy / d * 3; }
            });
        });
        function frame() {
            ctx.clearRect(0, 0, W, H);
            pts.forEach(function (p) {
                var dx = mouse.x - p.x, dy = mouse.y - p.y, d = Math.hypot(dx, dy);
                if (d < 160 && d > 0) { p.vx += dx / d * .02; p.vy += dy / d * .02; }
                p.vx *= .985; p.vy *= .985;
                if (Math.abs(p.vx) < .1) p.vx += (Math.random() - .5) * .02;
                if (Math.abs(p.vy) < .1) p.vy += (Math.random() - .5) * .02;
                p.x += p.vx; p.y += p.vy;
                if (p.x < 0 || p.x > W) p.vx *= -1;
                if (p.y < 0 || p.y > H) p.vy *= -1;
                ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, 7); ctx.fillStyle = 'rgba(144,202,249,.8)'; ctx.fill();
            });
            for (var i = 0; i < pts.length; i++) {
                for (var j = i + 1; j < pts.length; j++) {
                    var d = Math.hypot(pts[i].x - pts[j].x, pts[i].y - pts[j].y);
                    if (d < 120) { ctx.strokeStyle = 'rgba(144,202,249,' + ((1 - d / 120) * .25) + ')'; ctx.beginPath(); ctx.moveTo(pts[i].x, pts[i].y); ctx.lineTo(pts[j].x, pts[j].y); ctx.stroke(); }
                }
                var m = Math.hypot(pts[i].x - mouse.x, pts[i].y - mouse.y);
                if (m < 170) { ctx.strokeStyle = 'rgba(52,211,153,' + ((1 - m / 170) * .5) + ')'; ctx.beginPath(); ctx.moveTo(pts[i].x, pts[i].y); ctx.lineTo(mouse.x, mouse.y); ctx.stroke(); }
            }
            if (!reduced) requestAnimationFrame(frame);
        }
        frame();
    })();

    /* ---------- 3D tilt + magnetic / ripple buttons ---------- */
    if (!reduced) {
        addEventListener('mousemove', function (e) {
            var r = card.getBoundingClientRect(), px = (e.clientX - r.left) / r.width, py = (e.clientY - r.top) / r.height;
            if (px >= 0 && px <= 1 && py >= 0 && py <= 1 && innerWidth > 767) card.style.transform = 'rotateY(' + ((px - .5) * 5) + 'deg) rotateX(' + ((.5 - py) * 4) + 'deg)';
            else card.style.transform = '';
        });
        card.addEventListener('mouseleave', function () { card.style.transform = ''; });
        document.querySelectorAll('.btn-auth').forEach(function (b) {
            b.addEventListener('mousemove', function (e) {
                var r = b.getBoundingClientRect();
                b.style.transform = 'translate(' + ((e.clientX - r.left - r.width / 2) * .06) + 'px,' + ((e.clientY - r.top - r.height / 2) * .25 - 2) + 'px)';
            });
            b.addEventListener('mouseleave', function () { b.style.transform = ''; });
        });
    }
    document.querySelectorAll('.btn-auth').forEach(function (b) {
        b.addEventListener('click', function (e) {
            var r = b.getBoundingClientRect(), s = Math.max(r.width, r.height), sp = document.createElement('span');
            sp.className = 'ripple'; sp.style.cssText = 'width:' + s + 'px;height:' + s + 'px;left:' + (e.clientX - r.left - s / 2) + 'px;top:' + (e.clientY - r.top - s / 2) + 'px';
            b.appendChild(sp); setTimeout(function () { sp.remove(); }, 600);
        });
    });

    /* ---------- Links between the sign-in pages fade the page out first ---------- */
    document.querySelectorAll('a.back-login').forEach(function (a) {
        a.addEventListener('click', function (e) {
            if (reduced || e.ctrlKey || e.metaKey) return;
            e.preventDefault();
            a.classList.add('clicked'); a.innerHTML = 'Opening... <i class="bi bi-arrow-right ms-1"></i>';
            document.body.classList.add('page-exit');
            setTimeout(function () { location.href = a.href; }, 500);
        });
    });
    addEventListener('pageshow', function () { document.body.classList.remove('page-exit'); });

    /* ---------- Messages from the server ---------- */
    var data = document.getElementById('authData');
    if (data) {
        JSON.parse(data.dataset.errors || '[]').forEach(function (m) { window.authToast(m, 'error'); });
        if (data.dataset.errors && data.dataset.errors !== '[]') window.authShake();
        if (data.dataset.status) window.authToast(data.dataset.status, 'success');
    }
})();
</script>
@stack('scripts')
</body>
</html>
