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
.auth-card:hover{transform:translateY(-5px);box-shadow:0 45px 100px rgba(0,0,0,.5),0 0 60px rgba(33,150,243,.15),inset 0 1px 0 rgba(255,255,255,.4)}
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

.password-requirements{font-size:13px;margin-top:10px}
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
</style>
@stack('styles')
</head>
<body>

<div class="aurora-bg" aria-hidden="true">
    <div class="blob b1" data-d="30"></div><div class="blob b2" data-d="-45"></div><div class="blob b3" data-d="60"></div>
</div>
<div class="cursor-glow" id="cursorGlow" aria-hidden="true"></div>

<div class="auth-wrapper">
    <div class="auth-card" id="card">
        <div class="row g-0 align-items-stretch">

            <div class="col-md-5">
                <div class="brand-section">
                    <img src="{{ asset('images/assetone-logo.png') }}" alt="AssetOne Logo" class="brand-logo">
                    <h1 class="brand-title">AssetOne</h1>
                    <p class="brand-subtitle">MDPT Asset Management System</p>
                    <p class="brand-subtitle">@yield('brand-tagline')</p>
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
        if (greeting && greeting.dataset.greet !== undefined) greeting.textContent = h < 12 ? 'Good Morning' : h < 18 ? 'Good Afternoon' : 'Good Evening';
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
            if (!btn) return;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Please wait...';
        });
    });
})();
</script>
@stack('scripts')
</body>
</html>
