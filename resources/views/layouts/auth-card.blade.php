<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title') | AssetOne</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<style>
    * { box-sizing: border-box; }

    body {
        margin: 0;
        min-height: 100vh;
        font-family: "Segoe UI", Arial, sans-serif;
        background: linear-gradient(135deg, #0d1b2a 0%, #1b263b 50%, #0d47a1 100%);
        overflow-x: hidden;
        position: relative;
    }

    .area { position: absolute; top: 0; left: 0; width: 100%; height: 100%; overflow: hidden; z-index: 1; }
    .circles { position: absolute; top: 0; left: 0; width: 100%; height: 100%; margin: 0; padding: 0; list-style: none; }
    .circles li {
        position: absolute; display: block; list-style: none; width: 20px; height: 20px;
        background: rgba(255, 255, 255, 0.15); box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.2);
        backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.18); animation: authCircleFloat 25s linear infinite;
        bottom: -150px; border-radius: 50%;
    }
    .circles li:nth-child(1) { left: 25%; width: 80px; height: 80px; animation-delay: 0s; }
    .circles li:nth-child(2) { left: 10%; width: 30px; height: 30px; animation-delay: 2s; animation-duration: 12s; }
    .circles li:nth-child(3) { left: 70%; width: 20px; height: 20px; animation-delay: 4s; }
    .circles li:nth-child(4) { left: 40%; width: 60px; height: 60px; animation-delay: 0s; animation-duration: 18s; }
    .circles li:nth-child(5) { left: 65%; width: 20px; height: 20px; animation-delay: 0s; }
    .circles li:nth-child(6) { left: 75%; width: 110px; height: 110px; animation-delay: 3s; }
    .circles li:nth-child(7) { left: 35%; width: 150px; height: 150px; animation-delay: 7s; }
    .circles li:nth-child(8) { left: 50%; width: 25px; height: 25px; animation-delay: 15s; animation-duration: 45s; }
    .circles li:nth-child(9) { left: 20%; width: 15px; height: 15px; animation-delay: 2s; animation-duration: 35s; }
    .circles li:nth-child(10) { left: 85%; width: 150px; height: 150px; animation-delay: 0s; animation-duration: 11s; }
    @keyframes authCircleFloat {
        0% { transform: translateY(0) rotate(0deg); opacity: 1; border-radius: 50%; }
        100% { transform: translateY(-1000px) rotate(720deg); opacity: 0; border-radius: 50%; }
    }

    .auth-wrapper { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 30px 15px; position: relative; z-index: 2; }
    .auth-card {
        width: 100%; max-width: 1000px; min-height: 600px;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 24px; overflow: hidden; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
    }

    .brand-section {
        background: linear-gradient(135deg, rgba(21, 101, 192, 0.9), rgba(13, 71, 161, 0.95));
        color: #fff; padding: 50px 40px;
        display: flex; flex-direction: column; justify-content: center; align-items: center;
        text-align: center; height: 100%; backdrop-filter: blur(5px);
    }
    .brand-logo { width: 130px; max-width: 70%; margin-bottom: 25px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2)); }
    .brand-title { font-size: 32px; font-weight: 700; margin-bottom: 10px; letter-spacing: 0.5px; }
    .brand-subtitle { font-size: 16px; opacity: 0.9; line-height: 1.6; max-width: 350px; }
    .brand-icon { font-size: 55px; margin-top: 30px; opacity: 0.9; }

    .auth-section { padding: 50px 55px; display: flex; flex-direction: column; justify-content: center; background: transparent; }
    .auth-header { margin-bottom: 30px; }
    .auth-header h2 { font-size: 28px; font-weight: 700; color: #0d1b2a; margin-bottom: 8px; }
    .auth-header p { color: #5a6b82; margin-bottom: 0; line-height: 1.6; }

    .form-label { font-weight: 600; color: #1b263b; }
    .input-group-text { background-color: rgba(255, 255, 255, 0.6); border-color: rgba(21, 101, 192, 0.2); color: #1565C0; }
    .form-control { height: 48px; background-color: rgba(255, 255, 255, 0.7); border-color: rgba(21, 101, 192, 0.2); color: #0d1b2a; }
    .form-control:focus { background-color: rgba(255, 255, 255, 0.95); border-color: #1565C0; box-shadow: 0 0 0 0.25rem rgba(21,101,192,0.2); }

    .password-toggle { border: 1px solid rgba(21, 101, 192, 0.2); border-left: 0; background-color: rgba(255, 255, 255, 0.6); color: #1565C0; width: 50px; }
    .password-toggle:hover { background-color: rgba(255, 255, 255, 0.9); color: #0D47A1; }
    .password-toggle:focus { box-shadow: none; }

    .password-requirements { font-size: 13px; margin-top: 10px; }
    .password-requirements p { margin-bottom: 5px; color: #5a6b82; }
    .requirement { display: flex; align-items: center; gap: 6px; margin-bottom: 3px; color: #5a6b82; }
    .requirement i { font-size: 13px; }
    .requirement.valid { color: #198754; }

    .btn-auth {
        height: 48px; background: linear-gradient(135deg, #1565C0, #0D47A1); border: none; color: #fff;
        font-weight: 600; border-radius: 10px; box-shadow: 0 4px 12px rgba(13, 71, 161, 0.3); transition: all 0.3s ease;
    }
    .btn-auth:hover { background: linear-gradient(135deg, #0D47A1, #0a367a); color: #fff; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(13, 71, 161, 0.4); }
    .btn-auth:disabled { opacity: 0.8; transform: none; }

    .success-message { line-height: 1.6; }

    .back-login { color: #1565C0; text-decoration: none; font-weight: 600; }
    .back-login:hover { color: #0D47A1; text-decoration: underline; }
    .auth-footer { margin-top: 30px; text-align: center; font-size: 13px; color: #5a6b82; }

    @media (max-width: 767px) {
        .auth-wrapper { padding: 20px 15px; }
        .auth-card { min-height: auto; border-radius: 20px; }
        .brand-section { padding: 35px 25px; height: auto; }
        .brand-logo { width: 100px; }
        .brand-title { font-size: 25px; }
        .brand-icon { display: none; }
        .auth-section { padding: 35px 25px; }
        .auth-header h2 { font-size: 24px; }
    }
</style>
@stack('styles')
</head>
<body>

<div class="area">
    <ul class="circles">
        <li></li><li></li><li></li><li></li><li></li>
        <li></li><li></li><li></li><li></li><li></li>
    </ul>
</div>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="row g-0 h-100 align-items-stretch">

            <div class="col-md-5 brand-section">
                <img src="{{ asset('images/assetone-logo.png') }}" alt="AssetOne Logo" class="brand-logo">
                <h1 class="brand-title">AssetOne</h1>
                <p class="brand-subtitle">MDPT Asset Management System</p>
                <p class="brand-subtitle">@yield('brand-tagline')</p>
                <i class="bi @yield('brand-icon') brand-icon"></i>
            </div>

            <div class="col-md-7 auth-section">

                @if (session('error'))
                    <div class="alert alert-warning py-2 small">{{ session('error') }}</div>
                @endif

                @yield('content')

                <div class="auth-footer">
                    <p class="mb-1">&copy; {{ now()->year }} Majlis Daerah Perak Tengah</p>
                    <p class="mb-0">AssetOne Asset Management System</p>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.password-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const field = document.getElementById(btn.dataset.toggleTarget);
            const icon = btn.querySelector('i');
            const isPassword = field.type === 'password';
            field.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
            btn.setAttribute('aria-label', (isPassword ? 'Hide ' : 'Show ') + (btn.dataset.toggleLabel || 'password'));
        });
    });
</script>
@stack('scripts')
</body>
</html>
