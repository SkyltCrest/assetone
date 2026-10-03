<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Dashboard') | AssetOne</title>

<script>
    // Apply the saved theme before first paint to avoid a flash of the wrong theme.
    (function () {
        var mode = 'auto';
        try { mode = JSON.parse(localStorage.getItem('assetone_theme')) || 'auto'; } catch (e) {}
        if (mode === 'auto') {
            var d = new Date(), h = d.getHours() + d.getMinutes() / 60;
            mode = h >= 6.5 && h < 16 ? 'light' : h >= 16 && h < 19 ? 'dusk' : 'dark';
        }
        document.documentElement.setAttribute('data-theme', mode);
    })();
</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="{{ asset('css/assetone.css') }}?v={{ filemtime(public_path('css/assetone.css')) }}">
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
    $issueVerificationCount = ($user && $user->canManageAssets())
        ? \App\Models\IssueReport::where('status', \App\Models\IssueReport::STATUS_PENDING)->count()
        : 0;
    $roleLabel = ucwords(str_replace('_', ' ', $user->role ?? ''));
    $initials = collect(preg_split('/\s+/', trim($user->name ?? 'A')))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: 'A';

    $canManage = $user && $user->canManageAssets();
@endphp

<div class="bgl bgl-dark"></div><div class="bgl bgl-dusk"></div><div class="bgl bgl-light"></div>
<div id="eyeShade" aria-hidden="true"></div>
<div class="aurora" aria-hidden="true"><div class="blob b1" data-d="30"></div><div class="blob b2" data-d="-45"></div><div class="blob b3" data-d="60"></div></div>
<div class="cursor-glow" id="cursorGlow" aria-hidden="true"></div>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- Sign-out sequence overlay --}}
<div class="logout-overlay" id="logoutOverlay" aria-hidden="true" role="dialog" aria-live="polite" aria-label="Signing out">
    <div class="lo-orb o1"></div><div class="lo-orb o2"></div><div class="lo-orb o3"></div>
    <div class="logout-brand"><img src="{{ asset('images/assetone-logo.png') }}" alt="AssetOne"><span>AssetOne</span></div>
    <div class="lo-panel" id="logoutContent"></div>
</div>

{{-- Logout confirmation --}}
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content lo-card" id="loCard">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="lo-hero">
            <div class="lo-icon"><i class="bi bi-box-arrow-right"></i></div>
            <h5 id="logoutConfirmLabel">Ready to sign out?</h5>
            <p class="lo-sub">You'll need to sign in again to get back to your dashboard.</p>
        </div>
        <div class="lo-user">
            <div class="lo-av">@if($user?->photo)<img src="{{ $user->photoUrl() }}" alt="">@else{{ $initials }}@endif</div>
            <div><b>{{ $user->name ?? 'Guest' }}</b><small>{{ $roleLabel }}</small></div>
            <span class="lo-live"><i></i>Active</span>
        </div>
        <div class="lo-stats">
            <div><small>Session</small><b id="loDur">-</b></div>
            <div><small>Unread</small><b>{{ $unreadNotificationCount }}</b></div>
            <div><small>Theme</small><b id="loTheme">-</b></div>
        </div>
        <label class="lo-check"><input type="checkbox" id="loClear"><span></span>Also reset my display preferences (theme &amp; eye comfort)</label>
        <div class="lo-actions">
            <button type="button" class="btn lo-cancel" data-bs-dismiss="modal"><i class="bi bi-shield-check me-2"></i>Stay signed in</button>
            <button type="button" class="btn lo-confirm" id="confirmLogoutBtn"><span>Log out</span><i class="bi bi-arrow-right"></i></button>
        </div>
        <p class="lo-hint"><kbd>Esc</kbd> to cancel</p>
    </div></div>
</div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-brand text-center position-relative">
        <button type="button" class="btn-close btn-close-white d-lg-none position-absolute top-0 end-0" id="closeSidebarBtn" aria-label="Close"></button>
        <img src="{{ asset('images/assetone-logo.png') }}" class="sidebar-logo mb-2" alt="AssetOne Logo">
        <div class="sidebar-title">AssetOne</div>
        <div class="sidebar-subtitle">MDPT Asset Management</div>
    </div>

    <ul class="nav flex-column">
        <li class="sidebar-section">Overview</li>
        <li><a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i>Dashboard</a></li>

        @if($canManage)
        <li class="sidebar-section">
            <button type="button" class="sidebar-toggle" data-bs-toggle="collapse" data-bs-target="#menuAssetOps" aria-expanded="true">
                <span>Asset Operations</span><i class="bi bi-chevron-down"></i>
            </button>
        </li>
        <li class="collapse show" id="menuAssetOps">
            <ul class="nav flex-column">
                <li><a href="{{ route('assets.create') }}" class="nav-link {{ request()->routeIs('assets.create', 'assets.edit') ? 'active' : '' }}"><i class="bi bi-box-seam"></i>Asset Registration</a></li>
                <li><a href="{{ route('asset-management.index') }}" class="nav-link {{ request()->routeIs('asset-management.*', 'categories.*', 'locations.*', 'statuses.*') ? 'active' : '' }}"><i class="bi bi-tags"></i>Asset Management</a></li>
                <li><a href="{{ route('assignments.index') }}" class="nav-link {{ request()->routeIs('assignments.*') ? 'active' : '' }}"><i class="bi bi-person-check"></i>Assignment</a></li>
                <li><a href="{{ route('maintenance.index') }}" class="nav-link {{ request()->routeIs('maintenance.*') ? 'active' : '' }}"><i class="bi bi-tools"></i>Maintenance</a></li>
                <li><a href="{{ route('issue-verifications.index') }}" class="nav-link {{ request()->routeIs('issue-verifications.*') ? 'active' : '' }}"><i class="bi bi-patch-check"></i>Issue Verification @if($issueVerificationCount > 0)<span class="nav-count">{{ $issueVerificationCount }}</span>@endif</a></li>
            </ul>
        </li>
        @endif

        <li class="sidebar-section">
            <button type="button" class="sidebar-toggle" data-bs-toggle="collapse" data-bs-target="#menuWorkspace" aria-expanded="true">
                <span>My Workspace</span><i class="bi bi-chevron-down"></i>
            </button>
        </li>
        <li class="collapse show" id="menuWorkspace">
            <ul class="nav flex-column">
                <li><a href="{{ route('my-assignments.index') }}" class="nav-link {{ request()->routeIs('my-assignments.*') ? 'active' : '' }}"><i class="bi bi-clipboard-check"></i>My Assignments @if($myPendingCount > 0)<span class="nav-count">{{ $myPendingCount }}</span>@endif</a></li>
                <li><a href="{{ route('issues.index') }}" class="nav-link {{ request()->routeIs('issues.*') ? 'active' : '' }}"><i class="bi bi-exclamation-triangle"></i>Report Issues</a></li>
            </ul>
        </li>

        <li class="sidebar-section">
            <button type="button" class="sidebar-toggle" data-bs-toggle="collapse" data-bs-target="#menuReports" aria-expanded="true">
                <span>Reports &amp; Search</span><i class="bi bi-chevron-down"></i>
            </button>
        </li>
        <li class="collapse show" id="menuReports">
            <ul class="nav flex-column">
                <li><a href="{{ route('assets.index') }}" class="nav-link {{ request()->routeIs('assets.index', 'assets.show') ? 'active' : '' }}"><i class="bi bi-search"></i>Asset Overview</a></li>
                <li><a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-bar-graph"></i>Asset Report</a></li>
            </ul>
        </li>

        <li class="sidebar-section">
            <button type="button" class="sidebar-toggle" data-bs-toggle="collapse" data-bs-target="#menuAdmin" aria-expanded="true">
                <span>Administration</span><i class="bi bi-chevron-down"></i>
            </button>
        </li>
        <li class="collapse show" id="menuAdmin">
            <ul class="nav flex-column">
                @if($user && $user->isAdministrator())
                <li><a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="bi bi-people"></i>User Management</a></li>
                @endif
                <li><a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"><i class="bi bi-gear"></i>Settings</a></li>
            </ul>
        </li>

        <li class="mt-2">
            <form method="POST" action="{{ route('logout') }}" id="logoutForm" class="m-0">
                @csrf
                <button type="submit" class="nav-link border-0" id="logoutLink"><i class="bi bi-box-arrow-left"></i>Logout</button>
            </form>
        </li>
    </ul>
</div>

<div class="main-content">

    <div class="top-navbar g d-flex justify-content-between align-items-center animate-in delay-1">
        <div class="d-flex align-items-center gap-3">
            <button type="button" class="btn btn-light border d-lg-none" id="sidebarToggle" title="Toggle navigation" aria-label="Toggle navigation"><i class="bi bi-list fs-5"></i></button>
            <div>
                <h5 class="fw-bold mb-0">@yield('heading')</h5>
                <small class="text-muted">@yield('subheading')</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 gap-sm-3">
            <span class="live-clock d-none d-md-inline" id="clock"></span>

            <div class="nav-tool">
                <button type="button" class="btn btn-light btn-sm border position-relative" data-panel="notifPanel" title="Notifications" aria-label="Notifications">
                    <i class="bi bi-bell fs-6"></i>
                    @if($unreadNotificationCount > 0)
                        <span class="bell-badge">{{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}</span>
                    @endif
                </button>
                <div class="nav-panel notif-panel" id="notifPanel">
                    <div class="notif-head">
                        <strong><i class="bi bi-bell me-2"></i>Notifications</strong>
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
                        <a href="{{ route('notifications.index') }}" class="small fw-semibold">View all notifications</a>
                    </div>
                </div>
            </div>

            <div class="nav-tool">
                <button type="button" class="btn btn-light btn-sm border" id="eyeBtn" data-panel="eyePanel" title="Eye Comfort" aria-label="Eye Comfort"><i class="bi bi-eye fs-6"></i></button>
                <div class="nav-panel" id="eyePanel">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong><i class="bi bi-eye me-2"></i>Eye Comfort</strong>
                        <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" role="switch" id="eyeOn" data-eye="on" aria-label="Eye comfort on/off"></div>
                    </div>
                    <p class="small text-muted mb-3">Warms the screen colours to cut blue light.</p>
                    <label class="small fw-semibold d-flex justify-content-between" for="eyeLevel"><span>Warmth</span><span data-eye="value"></span></label>
                    <input type="range" class="form-range" id="eyeLevel" data-eye="level" min="0" max="100" step="5">
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="eyeAuto" data-eye="auto">
                        <label class="form-check-label small" for="eyeAuto">Auto: warmer in the evening &amp; at night</label>
                    </div>
                </div>
            </div>

            <div class="nav-tool">
                <button type="button" class="btn btn-light btn-sm border position-relative" id="themeBtn" aria-label="Toggle theme"><i class="bi fs-6" id="themeIcon"></i><span class="auto-tag" id="themeAuto">A</span></button>
            </div>

            <a href="{{ route('settings.index') }}" class="profile-chip d-flex align-items-center gap-2" title="Settings">
                <div class="text-end d-none d-sm-block">
                    <div class="fw-semibold lh-1 mb-1">{{ $user->name ?? 'Guest' }}</div>
                    <small class="text-muted">{{ $roleLabel }}</small>
                </div>
                <div class="profile-icon">@if($user?->photo)<img src="{{ $user->photoUrl() }}" alt="">@else{{ $initials }}@endif</div>
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
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

<footer class="footer">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center flex-wrap gap-2 text-center text-md-start">
            <img src="{{ asset('images/mdpt-logo.png') }}" alt="MDPT Logo" height="36" class="me-2">
            <span><strong>AssetOne</strong> &copy; {{ now()->year }} MDPT Asset Management System. All Rights Reserved.</span>
        </div>
    </div>
</footer>

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1080">
    <div id="liveToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/assetone.js') }}?v={{ filemtime(public_path('js/assetone.js')) }}"></script>
<script src="{{ asset('js/photo-picker.js') }}?v={{ filemtime(public_path('js/photo-picker.js')) }}"></script>
@stack('scripts')
</body>
</html>
