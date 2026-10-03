@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Asset Management Overview')

@section('content')

@php
    $user = auth()->user();
    $maintenanceBadges = ['pending' => 'secondary', 'in_progress' => 'warning', 'completed' => 'success'];
    $statusColors = ['success' => '#34d399', 'warning' => '#fbbf24', 'danger' => '#f87171', 'primary' => '#42a5f5', 'info' => '#22d3ee', 'secondary' => '#94a3b8', 'dark' => '#64748b'];
    $countFor = fn (string $name) => (int) optional($statusBreakdown->first(fn ($s) => strcasecmp($s->name, $name) === 0))->assets_count;
    $firstName = explode(' ', trim($user->name))[0];
    $hue = function (string $s) { $h = 0; foreach (str_split($s) as $c) { $h = ($h * 31 + ord($c)) % 360; } return $h; };
    $ini = fn (string $n) => collect(preg_split('/\s+/', trim($n)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $inactiveUserTotal = $userTotal - $activeUserTotal;
    $activePct = $userTotal ? round($activeUserTotal / $userTotal * 100) : 0;
    $statCards = [
        ['Total Assets', $assetTotal, 'blue', 'bi-box-seam', 2],
        ['Active Assets', $countFor('Active'), 'green', 'bi-check-circle', 3],
        ['Under Maintenance', $countFor('Under Maintenance'), 'orange', 'bi-tools', 4],
        ['Unavailable', $countFor('Unavailable'), 'red', 'bi-exclamation-circle', 5],
    ];
    $statusChartData = $statusBreakdown->where('assets_count', '>', 0)->values()
        ->map(fn ($s) => ['label' => $s->name, 'value' => $s->assets_count, 'color' => $statusColors[$s->badge_color] ?? '#94a3b8']);
    $categoryChartData = $categoryBreakdown->map(fn ($c) => ['label' => $c->name, 'value' => $c->assets_count]);
@endphp

<div class="page-banner mb-4 animate-in delay-2">
    <div class="page-banner-bg" aria-hidden="true"></div>
    <div class="page-banner-content">
        <h3 class="fw-bold mb-1" id="greeting" data-name="{{ $firstName }}">Welcome back, {{ $firstName }}! 👋</h3>
        <p>Here's an overview of your organisation's assets. You're signed in as <strong>{{ ucwords(str_replace('_', ' ', $user->role)) }}</strong>.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    @foreach($statCards as [$label, $value, $color, $icon, $delay])
    <div class="col-sm-6 col-xl-3 animate-in delay-{{ $delay }}">
        <div class="stat-card card-{{ $color }} d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted small text-uppercase mb-1">{{ $label }}</p>
                <h2 class="mb-0" data-count="{{ $value }}">{{ $value }}</h2>
            </div>
            <div class="stat-icon icon-{{ $color }}"><i class="bi {{ $icon }}"></i></div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-5 animate-in delay-4">
        <div class="content-card h-100 d-flex flex-column justify-content-between">
            <div class="mb-3"><h5 class="fw-bold mb-0">Asset Status Breakdown</h5><small class="text-muted">Share of assets by current condition</small></div>
            @if($assetTotal > 0)
                <div class="chart-container donut-box">
                    <div class="donut-wrapper">
                        <canvas id="statusChart"></canvas>
                        <div class="donut-center"><div class="donut-number">{{ $assetTotal }}</div><div class="donut-label">Total Assets</div></div>
                    </div>
                </div>
                <div class="status-legend">
                    @foreach($statusBreakdown->where('assets_count', '>', 0) as $status)
                        <div class="lg-item" style="--c:{{ $statusColors[$status->badge_color] ?? '#94a3b8' }}">
                            <div class="lg-top"><span class="lg-dot"></span>{{ $status->name }}</div>
                            <div class="lg-val">{{ $status->assets_count }}<small>{{ round($status->assets_count / $assetTotal * 100) }}%</small></div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="ru-empty"><i class="bi bi-pie-chart"></i>No assets registered yet.</div>
            @endif
        </div>
    </div>
    <div class="col-lg-7 animate-in delay-5">
        <div class="content-card h-100 d-flex flex-column justify-content-between">
            <div class="mb-3"><h5 class="fw-bold mb-0">Assets by Category</h5><small class="text-muted">Number of assets in each category</small></div>
            @if($categoryBreakdown->isNotEmpty())
                <div class="chart-container"><canvas id="categoryChart"></canvas></div>
            @else
                <div class="ru-empty"><i class="bi bi-bar-chart"></i>No categories defined yet.</div>
            @endif
        </div>
    </div>
</div>

<div class="content-card mb-4 animate-in delay-5">
    <h5 class="fw-bold mb-3">Quick Actions</h5>
    <div class="row g-3">
        @if($user->canManageAssets())
            <div class="col-sm-6 col-md-3"><a href="{{ route('assets.create') }}" class="btn btn-primary quick-action-btn w-100"><i class="bi bi-plus-circle"></i> Register Asset</a></div>
        @else
            <div class="col-sm-6 col-md-3"><a href="{{ route('my-assignments.index') }}" class="btn btn-primary quick-action-btn w-100"><i class="bi bi-clipboard-check"></i> My Assignments</a></div>
        @endif
        <div class="col-sm-6 col-md-3"><a href="{{ route('assets.index') }}" class="btn btn-outline-primary quick-action-btn w-100"><i class="bi bi-search"></i> Asset Overview</a></div>
        @if($user->canManageAssets())
            <div class="col-sm-6 col-md-3"><a href="{{ route('maintenance.index') }}" class="btn btn-outline-warning quick-action-btn w-100"><i class="bi bi-tools"></i> Log Maintenance</a></div>
        @else
            <div class="col-sm-6 col-md-3"><a href="{{ route('issues.index') }}" class="btn btn-outline-warning quick-action-btn w-100"><i class="bi bi-exclamation-triangle"></i> Report Issue</a></div>
        @endif
        <div class="col-sm-6 col-md-3"><a href="{{ route('reports.index') }}" class="btn btn-outline-success quick-action-btn w-100"><i class="bi bi-file-earmark-bar-graph"></i> View Reports</a></div>
    </div>
</div>

@if($user->canManageUsers())
<div class="row g-4 mb-4">
    <div class="col-lg-5 animate-in delay-6">
        <div class="content-card h-100">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                <div><h5 class="fw-bold mb-0">Total Users</h5><small class="text-muted">Account access overview</small></div>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-primary">Manage <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
            <div class="d-flex align-items-end gap-3 mb-3"><h2 class="fw-bold mb-0 usr-big">{{ $userTotal }}</h2><span class="text-muted small mb-2">registered accounts</span></div>
            <div class="usr-split" aria-hidden="true"><i class="a" style="--w:{{ $activePct }}%"></i><i class="d" style="--w:{{ $userTotal ? 100 - $activePct : 0 }}%"></i></div>
            <div class="usr-legend">
                <div class="lg-item" style="--c:#10b981"><div class="lg-top"><span class="lg-dot"></span>Active</div><div class="lg-val">{{ $activeUserTotal }}<small>{{ $activePct }}%</small></div></div>
                <div class="lg-item" style="--c:#ef4444"><div class="lg-top"><span class="lg-dot"></span>Deactivated</div><div class="lg-val">{{ $inactiveUserTotal }}<small>{{ $userTotal ? 100 - $activePct : 0 }}%</small></div></div>
            </div>
            <div class="text-muted small mb-2">By role</div>
            <div class="usr-roles">
                @foreach($roleBreakdown as $role => $count)
                    @php $roleName = ucwords(str_replace('_', ' ', $role)); @endphp
                    <span class="usr-role" style="--h:{{ $hue($roleName) }}">{{ $roleName }}<b>{{ $count }}</b></span>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-lg-7 animate-in delay-7">
        <div class="content-card h-100">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div><h5 class="fw-bold mb-0">Recent Users</h5><small class="text-muted">Latest accounts added</small></div>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-primary">View All <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
            <div class="ru-list">
                @forelse($recentUsers as $i => $u)
                    @php $on = $u->status === 'active'; @endphp
                    <a class="ru-item" href="{{ route('users.index') }}" style="--i:{{ $i }}">
                        <div class="ru-av" style="--h:{{ $hue((string) $u->id . $u->name) }}">{{ $ini($u->name) }}</div>
                        <div class="ru-main"><b>{{ $u->name }}</b><small>{{ $u->email }}</small></div>
                        <div class="ru-role d-none d-sm-block">{{ ucwords(str_replace('_', ' ', $u->role)) }}</div>
                        <span class="ru-status {{ $on ? 'on' : 'off' }}"><i></i>{{ $on ? 'Active' : 'Deactivated' }}</span>
                    </a>
                @empty
                    <div class="ru-empty"><i class="bi bi-person-x"></i>No users to show.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endif

<div class="content-card mb-4 animate-in delay-6">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h5 class="fw-bold mb-0">Recent Assets</h5>
        <a href="{{ route('assets.index') }}" class="btn btn-sm btn-outline-primary">View All <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Asset ID</th><th>Asset Name</th><th>Category</th><th>Location</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($recentAssets as $asset)
                    <tr class="row-in" style="animation-delay:{{ $loop->index * 50 }}ms">
                        <td class="fw-semibold"><a href="{{ route('assets.show', $asset) }}">{{ $asset->asset_code }}</a></td>
                        <td class="text-dark">{{ $asset->name }}</td>
                        <td>{{ $asset->category->name ?? '—' }}</td>
                        <td>{{ $asset->location->name ?? $asset->location_detail ?? '—' }}</td>
                        <td><span class="badge bg-{{ $asset->assetStatus->badge_color ?? 'secondary' }}">{{ $asset->assetStatus->name ?? 'Unknown' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No assets registered yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($recentMaintenance->isNotEmpty())
<div class="content-card mb-4 animate-in delay-7">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Recent Maintenance</h5>
        @if($user->canManageAssets())
            <a href="{{ route('maintenance.index') }}" class="btn btn-sm btn-outline-primary">View All <i class="bi bi-arrow-right ms-1"></i></a>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Asset ID</th><th>Asset Name</th><th>Issue</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($recentMaintenance as $record)
                    <tr class="row-in" style="animation-delay:{{ $loop->index * 50 }}ms">
                        <td class="fw-semibold">{{ $record->asset->asset_code ?? '—' }}</td>
                        <td class="text-dark">{{ $record->asset->name ?? '—' }}</td>
                        <td>{{ $record->description ?: ucfirst($record->type) }}</td>
                        <td>{{ $record->maintenance_date->format('d M Y') }}</td>
                        <td><span class="badge bg-{{ $maintenanceBadges[$record->status] ?? 'secondary' }}">{{ ucwords(str_replace('_', ' ', $record->status)) }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    if (!window.Chart) return;
    var statusData = @json($statusChartData);
    var categoryData = @json($categoryChartData);

    var PALETTE = {
        dark:  { def: 'rgba(255,255,255,.75)', tick: 'rgba(255,255,255,.55)', tickX: 'rgba(255,255,255,.85)', grid: 'rgba(255,255,255,.08)', gap: 'rgba(6,17,31,.85)' },
        dusk:  { def: 'rgba(255,255,255,.75)', tick: 'rgba(255,255,255,.55)', tickX: 'rgba(255,255,255,.85)', grid: 'rgba(255,255,255,.08)', gap: 'rgba(30,27,66,.85)' },
        light: { def: 'rgba(20,38,61,.75)', tick: 'rgba(20,38,61,.6)', tickX: 'rgba(20,38,61,.85)', grid: 'rgba(13,71,161,.1)', gap: 'rgba(255,255,255,.9)' }
    };
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    var charts = [];

    function render() {
        var C = PALETTE[window.aoTheme ? window.aoTheme() : 'dark'] || PALETTE.dark;
        Chart.defaults.color = C.def;
        charts.forEach(function (c) { c.destroy(); }); charts = [];

        var s = document.getElementById('statusChart');
        if (s && statusData.length) charts.push(new Chart(s, {
            type: 'doughnut',
            data: { labels: statusData.map(function (d) { return d.label; }),
                    datasets: [{ data: statusData.map(function (d) { return d.value; }), backgroundColor: statusData.map(function (d) { return d.color; }), borderColor: C.gap, borderWidth: 3, hoverOffset: 8, borderRadius: 6 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '72%', plugins: { legend: { display: false } } }
        }));

        var c = document.getElementById('categoryChart');
        if (c && categoryData.length) {
            var ctx = c.getContext('2d'), grad = ctx.createLinearGradient(0, 0, 0, 320);
            grad.addColorStop(0, 'rgba(66,165,245,.95)'); grad.addColorStop(1, 'rgba(13,71,161,.55)');
            charts.push(new Chart(c, {
                type: 'bar',
                data: { labels: categoryData.map(function (d) { return d.label; }),
                        datasets: [{ label: 'Assets', data: categoryData.map(function (d) { return d.value; }), backgroundColor: grad, borderRadius: 10, borderSkipped: false, maxBarThickness: 46 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0, color: C.tick }, grid: { color: C.grid }, border: { display: false } },
                              x: { ticks: { color: C.tickX }, grid: { display: false }, border: { display: false } } } }
            }));
        }
    }
    render();
    document.addEventListener('ao:theme', render);

    // Count-up for the stat cards.
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.querySelectorAll('.stat-card [data-count]').forEach(function (el) {
            var to = +el.dataset.count, t0 = performance.now();
            if (!to) return;
            (function f(t) { var p = Math.min((t - t0) / 900, 1); el.textContent = Math.round(to * (1 - Math.pow(1 - p, 3))); if (p < 1) requestAnimationFrame(f); })(t0);
        });
    }
})();
</script>
@endpush
