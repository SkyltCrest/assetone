<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Dashboard') | AssetOne</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

<style>
    :root{
        --navy:#0B3159; --blue:#1565C0; --teal:#0EA5A0; --bg:#F4F6FA; --text:#152238; --muted:#6B7A90;
        --success:#16A34A; --success-bg:#E9F9EF; --warning:#F59E0B; --warning-bg:#FEF6E7;
        --danger:#E5484D; --danger-bg:#FDECEC; --info-bg:#EAF2FD;
        --card-radius:16px; --shadow:0 1px 2px rgba(16,24,40,0.04), 0 8px 24px rgba(16,24,40,0.06);
    }
    *{ font-family:'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    body{ background:var(--bg) !important; color:var(--text); }
    a{ text-decoration:none; }

    .sidebar { width: 264px; height: 100vh; position: fixed; top: 0; left: 0;
        background: linear-gradient(190deg, var(--navy) 0%, var(--blue) 100%); color: white;
        padding: 24px 16px; overflow-y: auto; display:flex; flex-direction:column; }
    .sidebar-brand{ display:flex; align-items:center; gap:12px; padding:4px 10px 24px 10px;
        border-bottom:1px solid rgba(255,255,255,0.12); margin-bottom:18px; }
    .sidebar-brand .logo-badge{ width:42px; height:42px; min-width:42px; background:#fff; border-radius:11px;
        display:flex; align-items:center; justify-content:center; }
    .sidebar-brand img{ width:28px; }
    .sidebar-brand .name{ font-weight:800; font-size:1.05rem; line-height:1.1; }
    .sidebar-brand .sub{ opacity:0.7; font-size:0.72rem; }
    .nav-section-label{ font-size:0.68rem; font-weight:700; letter-spacing:0.09em; text-transform:uppercase;
        color:rgba(255,255,255,0.45); padding:14px 14px 6px; }
    .sidebar .nav-link { color: rgba(255,255,255,0.82); padding: 10px 14px; border-radius: 10px; margin-bottom: 3px;
        font-size:0.92rem; font-weight:500; transition: 0.18s; display:flex; align-items:center; position:relative; }
    .sidebar .nav-link:hover{ background: rgba(255,255,255,0.10); color: white; }
    .sidebar .nav-link.active{ background: rgba(255,255,255,0.16); color: white; font-weight:600; }
    .sidebar .nav-link.active::before{ content:""; position:absolute; left:-4px; top:8px; bottom:8px; width:4px;
        border-radius:4px; background:var(--teal); }
    .sidebar .nav-link i { margin-right: 12px; width: 18px; font-size:1rem; text-align:center; }

    .main-content { margin-left: 264px; padding: 24px 28px 40px; }
    .topbar{ background:#fff; border-radius:var(--card-radius); box-shadow:var(--shadow); padding:16px 22px;
        display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:16px; flex-wrap:wrap; }
    .topbar h5{ font-weight:800; margin-bottom:2px; letter-spacing:-0.01em; }
    .topbar small{ color:var(--muted); }
    .topbar-search{ position:relative; min-width:230px; }
    .topbar-search i{ position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--muted); }
    .topbar-search input{ border-radius:10px; border:1.5px solid #E7EAF1; padding:9px 14px 9px 38px;
        background:#F8FAFC; font-size:0.88rem; width:100%; }
    .icon-btn{ width:40px; height:40px; border-radius:10px; background:#F4F6FA; display:flex; align-items:center;
        justify-content:center; color:var(--text); position:relative; font-size:1.05rem; }
    .icon-btn .dot{ position:absolute; top:8px; right:9px; width:7px; height:7px; border-radius:50%;
        background:var(--danger); border:1.5px solid #fff; }
    .profile-chip{ display:flex; align-items:center; gap:10px; padding:5px 10px 5px 5px; border-radius:12px; cursor:pointer; }
    .profile-chip:hover{ background:#F4F6FA; }
    .avatar-badge{ width: 38px; height: 38px; background: linear-gradient(135deg, var(--blue), var(--teal));
        color: white; border-radius: 11px; display: flex; align-items: center; justify-content: center;
        font-weight:700; font-size:0.95rem; }

    .stat-card { background:#fff !important; border: none !important; border-radius: var(--card-radius) !important;
        box-shadow: var(--shadow) !important; padding: 20px 22px !important; position:relative; overflow:hidden; transition:.2s ease; }
    .stat-card::before{ content:""; position:absolute; top:0; left:0; right:0; height:4px; background:transparent; }
    .stat-card:has(.icon-blue)::before{ background:var(--blue); }
    .stat-card:has(.icon-green)::before{ background:var(--success); }
    .stat-card:has(.icon-orange)::before{ background:var(--warning); }
    .stat-card:has(.icon-red)::before{ background:var(--danger); }
    .stat-card:hover{ transform: translateY(-3px); box-shadow:0 4px 6px rgba(16,24,40,0.05), 0 14px 30px rgba(16,24,40,0.09) !important; }
    .stat-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 21px; }
    .icon-blue { background: var(--info-bg); color: var(--blue); }
    .icon-green { background: var(--success-bg); color: var(--success); }
    .icon-orange { background: var(--warning-bg); color: var(--warning); }
    .icon-red { background: var(--danger-bg); color: var(--danger); }
    .stat-card p.text-muted{ color:var(--muted) !important; font-size:0.85rem; }
    .stat-card h3{ font-weight:800 !important; letter-spacing:-0.02em; }

    .content-card, .form-card{ background:#fff !important; border: none !important; border-radius: var(--card-radius) !important;
        box-shadow: var(--shadow) !important; padding: 25px !important; }
    .content-card h5, .content-card h3{ font-weight:800; letter-spacing:-0.01em; }
    .section-title{ color: var(--blue) !important; font-weight:700 !important; }
    .required{ color: var(--danger) !important; }
    .btn-save{ background: linear-gradient(135deg, var(--blue) 0%, var(--navy) 100%) !important; color: #fff !important;
        font-weight:700 !important; border:none !important; border-radius:10px !important; box-shadow:0 8px 20px rgba(21,101,192,0.24); }
    .btn-save:hover{ color:#fff !important; transform:translateY(-1px); }

    .table thead th{ font-size:0.72rem; text-transform:uppercase; letter-spacing:0.06em; color:var(--muted); font-weight:700;
        border-bottom:1.5px solid #EEF1F6; padding-bottom:12px; }
    .table td{ padding-top:14px; padding-bottom:14px; border-bottom:1px solid #F3F5F9; font-size:0.9rem; vertical-align:middle; }
    .table tbody tr:hover{ background:#FAFBFD; }
    .table tbody tr:last-child td{ border-bottom:none; }

    .badge{ border-radius:20px !important; font-weight:700 !important; font-size:0.74rem !important; padding:6px 12px !important; }
    .badge.bg-success{ background-color:var(--success-bg) !important; color:var(--success) !important; }
    .badge.bg-warning{ background-color:var(--warning-bg) !important; color:#B45309 !important; }
    .badge.bg-danger{ background-color:var(--danger-bg) !important; color:var(--danger) !important; }
    .badge.bg-secondary{ background-color:#EEF1F6 !important; color:var(--muted) !important; }
    .badge.bg-dark{ background-color:#E7EAF1 !important; color:var(--text) !important; }
    .badge.bg-info{ background-color:var(--info-bg) !important; color:var(--blue) !important; }
    .badge.bg-primary{ background-color:var(--info-bg) !important; color:var(--blue) !important; }

    .btn{ border-radius:10px; font-weight:600; }
    .btn-primary{ background:var(--blue) !important; border-color:var(--blue) !important; }
    .btn-primary:hover{ background:var(--navy) !important; border-color:var(--navy) !important; }
    .btn-outline-primary{ color:var(--blue) !important; border-color:var(--blue) !important; }
    .btn-outline-primary:hover{ background:var(--blue) !important; border-color:var(--blue) !important; }
    .btn-outline-danger{ color:var(--danger) !important; border-color:var(--danger) !important; }
    .btn-outline-danger:hover{ background:var(--danger) !important; border-color:var(--danger) !important; }
    .btn-outline-warning{ color:#B45309 !important; border-color:var(--warning) !important; }
    .btn-outline-warning:hover{ background:var(--warning) !important; border-color:var(--warning) !important; color:#fff !important; }
    .btn-outline-success{ color:var(--success) !important; border-color:var(--success) !important; }
    .btn-outline-success:hover{ background:var(--success) !important; border-color:var(--success) !important; }

    .form-control, .form-select{ border-radius:9px; border-color:#E4E8F0; }
    .form-control:focus, .form-select:focus{ border-color:var(--blue); box-shadow:0 0 0 3px rgba(21,101,192,0.12); }
    .input-group-text{ background:#F8FAFC; border-color:#E4E8F0; }

    .modal-content{ border:none; border-radius:16px; box-shadow:0 20px 50px rgba(16,24,40,0.18); }
    .modal-header{ border-bottom:1px solid #F0F2F6; }
    .modal-footer{ border-top:1px solid #F0F2F6; }

    .page-link{ color:var(--blue); border-color:#E7EAF1; }
    .page-item.active .page-link{ background:var(--blue); border-color:var(--blue); }
    .page-link:hover{ color:var(--navy); }

    .notif-dropdown{ width:360px; max-width:92vw; padding:0; border:none; border-radius:14px;
        box-shadow:0 20px 50px rgba(16,24,40,0.18); overflow:hidden; }
    .notif-dropdown .notif-head{ padding:14px 16px; border-bottom:1px solid #F0F2F6; display:flex;
        justify-content:space-between; align-items:center; }
    .notif-dropdown .notif-head strong{ font-size:0.95rem; }
    .notif-list{ max-height:380px; overflow-y:auto; }
    .notif-item{ display:block; padding:12px 16px; border-bottom:1px solid #F3F5F9; color:var(--text);
        font-size:0.85rem; line-height:1.4; white-space:normal; }
    .notif-item:hover{ background:#F7F9FC; color:var(--text); }
    .notif-item.unread{ background:#F4F8FF; }
    .notif-item .notif-title{ font-weight:700; display:block; margin-bottom:2px; }
    .notif-item .notif-time{ color:var(--muted); font-size:0.75rem; }
    .notif-empty{ padding:26px 16px; text-align:center; color:var(--muted); font-size:0.85rem; }
    .notif-foot{ padding:10px 16px; text-align:center; border-top:1px solid #F0F2F6; }
    .icon-btn .count-badge{ position:absolute; top:2px; right:2px; min-width:17px; height:17px; padding:0 4px;
        border-radius:9px; background:var(--danger); color:#fff; font-size:0.66rem; font-weight:700;
        display:flex; align-items:center; justify-content:center; border:1.5px solid #fff; }
    .nav-link .nav-count{ margin-left:auto; background:var(--teal); color:#fff; font-size:0.68rem; font-weight:700;
        border-radius:9px; padding:1px 7px; }

    @media print { .sidebar, .topbar, .no-print { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 10px !important; }
        .stat-card, .content-card { box-shadow: none !important; border: 1px solid #ddd !important; }
        .btn { display: none !important; } }

    .sidebar-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(11,49,89,0.45);
        z-index: 1050; display: none; opacity: 0; transition: opacity 0.2s ease; }
    .sidebar-overlay.show { display: block; opacity: 1; }
    .sidebar-close-btn { display: none; }

    @media (max-width: 991.98px) {
        .sidebar { transform: translateX(-100%); transition: transform 0.3s ease-in-out; z-index: 1055; }
        .sidebar.show { transform: translateX(0); }
        .sidebar-close-btn { display: inline-flex; }
        .main-content { margin-left: 0; padding: 18px; }
        .topbar { margin-bottom: 18px; }
    }
</style>
@stack('styles')
</head>
<body>

@php
    $user = auth()->user();
    $unreadNotifications = $user ? $user->unreadNotifications()->latest()->take(8)->get() : collect();
    $unreadNotificationCount = $user ? $user->unreadNotifications()->count() : 0;
    $myPendingCount = $user
        ? \App\Models\AssetAssignment::where('custodian_id', $user->id)
            ->where('status', \App\Models\AssetAssignment::STATUS_PENDING)->count()
        : 0;
@endphp

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="logo-badge"><img src="{{ asset('images/assetone-logo.png') }}" alt="Logo"></div>
        <div>
            <div class="name">AssetOne</div>
            <div class="sub">MDPT Asset Management</div>
        </div>
        <button type="button" class="btn-close btn-close-white sidebar-close-btn ms-auto" id="sidebarCloseBtn" aria-label="Close"></button>
    </div>

    <ul class="nav flex-column">
        <div class="nav-section-label">Overview</div>
        <li class="nav-item"><a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Home</a></li>

        <div class="nav-section-label">Asset Operations</div>
        @if($user && $user->canManageAssets())
        <li class="nav-item"><a href="{{ route('assets.create') }}" class="nav-link {{ request()->routeIs('assets.create') || request()->routeIs('assets.edit') ? 'active' : '' }}"><i class="bi bi-box-seam"></i> Asset Registration</a></li>
        <li class="nav-item"><a href="{{ route('asset-management.index') }}" class="nav-link {{ request()->routeIs('asset-management.*') || request()->routeIs('categories.*') || request()->routeIs('locations.*') || request()->routeIs('statuses.*') ? 'active' : '' }}"><i class="bi bi-tags"></i> Asset Management</a></li>
        <li class="nav-item"><a href="{{ route('assignments.index') }}" class="nav-link {{ request()->routeIs('assignments.*') ? 'active' : '' }}"><i class="bi bi-person-check"></i> Assignment</a></li>
        <li class="nav-item"><a href="{{ route('maintenance.index') }}" class="nav-link {{ request()->routeIs('maintenance.*') ? 'active' : '' }}"><i class="bi bi-tools"></i> Maintenance</a></li>
        @endif
        <li class="nav-item"><a href="{{ route('my-assignments.index') }}" class="nav-link {{ request()->routeIs('my-assignments.*') ? 'active' : '' }}"><i class="bi bi-clipboard-check"></i> My Assignments @if($myPendingCount > 0)<span class="nav-count">{{ $myPendingCount }}</span>@endif</a></li>
        <li class="nav-item"><a href="{{ route('assets.index') }}" class="nav-link {{ request()->routeIs('assets.index') ? 'active' : '' }}"><i class="bi bi-search"></i> Search &amp; Filter</a></li>
        <li class="nav-item"><a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-bar-graph"></i> Asset Report</a></li>

        @if($user && $user->isAdministrator())
        <div class="nav-section-label">Administration</div>
        <li class="nav-item"><a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="bi bi-people"></i> User Management</a></li>
        @endif
        <li class="nav-item"><a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"><i class="bi bi-gear"></i> Settings</a></li>

        <li class="nav-item mt-2">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link border-0 w-100 text-start bg-transparent"><i class="bi bi-box-arrow-left"></i> Logout</button>
            </form>
        </li>
    </ul>
</div>

<div class="main-content">

    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button type="button" class="btn btn-light border d-lg-none" id="sidebarToggleBtn" aria-label="Toggle navigation">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <h5>@yield('heading')</h5>
                <small>@yield('subheading')</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="dropdown">
                <button type="button" class="icon-btn border-0" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Notifications">
                    <i class="bi bi-bell"></i>
                    @if($unreadNotificationCount > 0)
                        <span class="count-badge">{{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}</span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end notif-dropdown">
                    <div class="notif-head">
                        <strong>Notifications</strong>
                        @if($unreadNotificationCount > 0)
                            <form method="POST" action="{{ route('notifications.readAll') }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none">Mark all read</button>
                            </form>
                        @endif
                    </div>
                    <div class="notif-list">
                        @forelse($unreadNotifications as $notification)
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="m-0">
                                @csrf
                                <button type="submit" class="notif-item unread border-0 bg-transparent w-100 text-start">
                                    <span class="notif-title">{{ $notification->data['title'] ?? 'Notification' }}</span>
                                    <span>{{ $notification->data['message'] ?? '' }}</span><br>
                                    <span class="notif-time">{{ $notification->created_at->diffForHumans() }}</span>
                                </button>
                            </form>
                        @empty
                            <div class="notif-empty"><i class="bi bi-check2-circle fs-4 d-block mb-1"></i>You're all caught up.</div>
                        @endforelse
                    </div>
                    <div class="notif-foot">
                        <a href="{{ route('notifications.index') }}" class="text-decoration-none small fw-semibold">View all notifications</a>
                    </div>
                </div>
            </div>
            <a href="{{ route('settings.index') }}" class="profile-chip text-decoration-none text-reset">
                <div class="avatar-badge">{{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}</div>
                <div class="d-none d-sm-block">
                    <div class="fw-semibold" style="font-size:0.88rem; line-height:1.1;">{{ $user->name ?? 'Guest' }}</div>
                    <small class="text-muted" style="font-size:0.74rem;">{{ ucwords(str_replace('_', ' ', $user->role ?? '')) }}</small>
                </div>
                <i class="bi bi-chevron-down text-muted small d-none d-sm-block"></i>
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var toggleBtn = document.getElementById('sidebarToggleBtn');
    var closeBtn = document.getElementById('sidebarCloseBtn');

    function openSidebar() {
        sidebar.classList.add('show');
        overlay.classList.add('show');
    }

    function closeSidebar() {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    }

    if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) closeSidebar();
    });
})();
</script>
@stack('scripts')
</body>
</html>
