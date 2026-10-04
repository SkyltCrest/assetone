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
    // Three groups, as on the status chart: active, under maintenance, and everything else.
    $maintenanceTotal = $countFor('Under Maintenance');
    $activeTotal = $countFor('Active');
    $trendStats = ['total' => $assetTotal, 'active' => $activeTotal, 'maintenance' => $maintenanceTotal, 'unavailable' => max(0, $assetTotal - $activeTotal - $maintenanceTotal)];
    $statCards = [
        ['Total Assets', $assetTotal, 'blue', 'bi-box-seam', 2, 'total'],
        ['Active Assets', $trendStats['active'], 'green', 'bi-check-circle', 3, 'active'],
        ['Under Maintenance', $trendStats['maintenance'], 'orange', 'bi-tools', 4, 'maintenance'],
        ['Unavailable', $trendStats['unavailable'], 'red', 'bi-exclamation-circle', 5, 'unavailable'],
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
    @foreach($statCards as [$label, $value, $color, $icon, $delay, $key])
    <div class="col-sm-6 col-xl-3 animate-in delay-{{ $delay }}">
        <div class="stat-card card-{{ $color }} d-flex justify-content-between align-items-center" style="--ac:{{ ['blue' => '#42a5f5', 'green' => '#34d399', 'orange' => '#fbbf24', 'red' => '#f87171'][$color] }}">
            <div>
                <p class="text-muted small text-uppercase mb-1">{{ $label }}</p>
                <h2 class="mb-0" data-count="{{ $value }}">{{ $value }}</h2>
                <div class="stat-trend trend-neutral" id="trend-{{ $key }}"><i class="bi bi-dash-circle"></i><span>No change</span></div>
                <div class="spark" id="spark-{{ $key }}"></div>
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
                    @foreach([['Active', $trendStats['active'], '#10b981'], ['Maintenance', $trendStats['maintenance'], '#f59e0b'], ['Unavailable', $trendStats['unavailable'], '#ef4444']] as [$name, $n, $colour])
                        <div class="lg-item" style="--c:{{ $colour }}">
                            <div class="lg-top"><span class="lg-dot"></span>{{ $name }}</div>
                            <div class="lg-val">{{ $n }}<small>{{ round($n / $assetTotal * 100) }}%</small></div>
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
                <button type="button" class="lg-item usr-chip" data-f="active" style="--c:#10b981" aria-label="Show active users"><div class="lg-top"><span class="lg-dot"></span>Active</div><div class="lg-val">{{ $activeUserTotal }}<small>{{ $activePct }}%</small></div></button>
                <button type="button" class="lg-item usr-chip" data-f="inactive" style="--c:#ef4444" aria-label="Show deactivated users"><div class="lg-top"><span class="lg-dot"></span>Deactivated</div><div class="lg-val">{{ $inactiveUserTotal }}<small>{{ $userTotal ? 100 - $activePct : 0 }}%</small></div></button>
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
                <div><h5 class="fw-bold mb-0">Recent Users</h5><small class="text-muted" id="ruSub">Latest accounts added</small></div>
                <div class="d-flex align-items-center gap-2">
                    <span class="ru-filter" id="ruFilter"><span id="ruFilterTxt"></span><button type="button" id="ruClear" aria-label="Clear filter"><i class="bi bi-x"></i></button></span>
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-primary">View All <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
            <div class="ru-list" id="recentUsers">
                @forelse($recentUsers as $i => $u)
                    @php $on = $u->status === 'active'; @endphp
                    <a class="ru-item" href="{{ route('users.index') }}" style="--i:{{ min($i, 5) }}" data-status="{{ $u->status }}" title="Open User Management">
                        <div class="ru-av" style="--h:{{ $hue((string) $u->id . $u->name) }}">@if($u->photo)<img src="{{ $u->photoUrl() }}" alt="">@else{{ $ini($u->name) }}@endif</div>
                        <div class="ru-main"><b>{{ $u->name }}</b><small>{{ $u->email }}</small></div>
                        <div class="ru-role d-none d-sm-block">{{ ucwords(str_replace('_', ' ', $u->role)) }}</div>
                        <span class="ru-status {{ $on ? 'on' : 'off' }}"><i></i>{{ $on ? 'Active' : 'Deactivated' }}</span>
                    </a>
                @empty
                @endforelse
                <div class="ru-empty d-none" id="ruEmpty"><i class="bi bi-person-x"></i>No users to show.</div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="content-card mb-4 animate-in delay-6">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h5 class="fw-bold mb-0">Recent Assets</h5>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="search-box"><i class="bi bi-search"></i><input type="search" id="assetSearch" class="form-control" placeholder="Search assets..." aria-label="Search assets"></div>
            <a href="{{ route('assets.index') }}" class="btn btn-sm btn-outline-primary">View All <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Asset ID</th><th>Asset Name</th><th>Category</th><th>Location</th><th>Status</th></tr></thead>
            <tbody id="recentAssets">
                @forelse($recentAssets as $asset)
                    <tr class="row-in" data-row style="animation-delay:{{ $loop->index * 50 }}ms">
                        <td class="fw-semibold"><a href="{{ route('assets.show', $asset) }}">{{ $asset->asset_code }}</a></td>
                        <td class="text-dark">{{ $asset->name }}</td>
                        <td>{{ $asset->category->name ?? '—' }}</td>
                        <td>{{ $asset->location->name ?? $asset->location_detail ?? '—' }}</td>
                        <td><span class="badge bg-{{ $asset->assetStatus->badge_color ?? 'secondary' }}">{{ $asset->assetStatus->name ?? 'Unknown' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No assets registered yet.</td></tr>
                @endforelse
                <tr id="recentAssetsNone" class="d-none"><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-search me-2"></i>No recent assets match "<span></span>". Press Enter to search all assets.</td></tr>
            </tbody>
        </table>
    </div>
</div>

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
                @forelse($recentMaintenance as $record)
                    <tr class="row-in" style="animation-delay:{{ $loop->index * 50 }}ms">
                        <td class="fw-semibold">{{ $record->asset->asset_code ?? '—' }}</td>
                        <td class="text-dark">{{ $record->asset->name ?? '—' }}</td>
                        <td>{{ $record->description ?: ucfirst($record->type) }}</td>
                        <td>{{ $record->maintenance_date->format('d M Y') }}</td>
                        <td><span class="badge bg-{{ $maintenanceBadges[$record->status] ?? 'secondary' }}">{{ ucwords(str_replace('_', ' ', $record->status)) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No maintenance recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('nav-tools')
    <button type="button" class="btn btn-light btn-sm border" id="refreshBtn" title="Refresh Dashboard Data" aria-label="Refresh Dashboard Data"><i class="bi bi-arrow-clockwise fs-6" id="refreshIcon"></i></button>
@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
    var stats = @json($trendStats);          // { total, active, maintenance, unavailable }
    var categoryData = @json($categoryChartData);

    /* ---------- Daily snapshots (kept in this browser) drive the trends and sparklines ---------- */
    var HISTORY_KEY = 'assetone_stats_history', MAX_DAYS = 30;
    function today() { var d = new Date(); return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); }
    function history() { try { return JSON.parse(localStorage.getItem(HISTORY_KEY) || '{}') || {}; } catch (e) { return {}; } }
    function snapshot() {
        var h = history(); h[today()] = Object.assign({}, stats, { savedAt: new Date().toISOString() });
        var dates = Object.keys(h).sort();
        if (dates.length > MAX_DAYS) dates.slice(0, dates.length - MAX_DAYS).forEach(function (d) { delete h[d]; });
        try { localStorage.setItem(HISTORY_KEY, JSON.stringify(h)); } catch (e) {}
        return h;
    }
    function trend(key, upClass, downClass) {
        var el = $('trend-' + key); if (!el) return;
        var h = history(), past = Object.keys(h).filter(function (d) { return d < today(); }).sort().reverse();
        if (!past.length) {
            el.className = 'stat-trend trend-neutral';
            el.innerHTML = '<i class="bi bi-stars"></i><span>Baseline set</span>';
            el.title = "This is the first run. Today's data will be used as the baseline for tomorrow's comparison.";
            return;
        }
        var prev = h[past[0]][key] || 0, cur = stats[key], diff = cur - prev, when = past[0];
        if (diff > 0) { el.className = 'stat-trend ' + upClass; el.innerHTML = '<i class="bi bi-arrow-up-circle-fill"></i><span>+' + diff + ' today</span>'; el.title = 'Increased by ' + diff + ' compared to ' + when + ' (' + prev + ' → ' + cur + ')'; }
        else if (diff < 0) { el.className = 'stat-trend ' + downClass; el.innerHTML = '<i class="bi bi-arrow-down-circle-fill"></i><span>' + diff + ' today</span>'; el.title = 'Decreased by ' + Math.abs(diff) + ' compared to ' + when + ' (' + prev + ' → ' + cur + ')'; }
        else { el.className = 'stat-trend trend-neutral'; el.innerHTML = '<i class="bi bi-dash-circle"></i><span>No change</span>'; el.title = 'No change compared to ' + when + ' (' + prev + ')'; }
    }
    function spark(key) {
        var el = $('spark-' + key); if (!el) return;
        var h = history(), v = Object.keys(h).sort().slice(-14).map(function (d) { return h[d][key] || 0; });
        if (v.length < 2) { el.innerHTML = ''; return; }
        var mx = Math.max.apply(null, v), mn = Math.min.apply(null, v), r = mx - mn || 1;
        var pts = v.map(function (y, i) { return (i / (v.length - 1) * 100).toFixed(1) + ',' + (22 - (y - mn) / r * 18).toFixed(1); }).join(' ');
        el.innerHTML = '<svg viewBox="0 0 100 26" preserveAspectRatio="none"><polyline points="' + pts + '" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    }
    snapshot();
    trend('total', 'trend-up', 'trend-down'); trend('active', 'trend-up', 'trend-down');
    trend('maintenance', 'trend-warning', 'trend-up'); trend('unavailable', 'trend-down', 'trend-up');
    ['total', 'active', 'maintenance', 'unavailable'].forEach(spark);

    /* ---------- Charts ---------- */
    if (window.Chart) {
        var PALETTE = {
            dark:  { def: 'rgba(255,255,255,.75)', tick: 'rgba(255,255,255,.55)', tickX: 'rgba(255,255,255,.85)', grid: 'rgba(255,255,255,.08)' },
            dusk:  { def: 'rgba(255,255,255,.75)', tick: 'rgba(255,255,255,.55)', tickX: 'rgba(255,255,255,.85)', grid: 'rgba(255,255,255,.08)' },
            light: { def: 'rgba(20,38,61,.75)', tick: 'rgba(20,38,61,.6)', tickX: 'rgba(20,38,61,.85)', grid: 'rgba(13,71,161,.1)' }
        };
        var STATUS_META = [{ k: 'Active', c: '#10b981' }, { k: 'Maintenance', c: '#f59e0b' }, { k: 'Unavailable', c: '#ef4444' }];
        var CAT_COLORS = ['#3b82f6', '#6366f1', '#06b6d4', '#8b5cf6', '#0ea5e9', '#ec4899', '#14b8a6'];
        var tipBase = { backgroundColor: 'rgba(6,17,31,.94)', borderColor: 'rgba(144,202,249,.35)', borderWidth: 1, padding: 12, cornerRadius: 10, titleFont: { size: 13, weight: '700' }, bodyFont: { size: 12, weight: '500' }, boxPadding: 6 };
        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
        var charts = [];

        function render() {
            var C = PALETTE[window.aoTheme ? window.aoTheme() : 'dark'] || PALETTE.dark;
            Chart.defaults.color = C.def;
            charts.forEach(function (c) { c.destroy(); }); charts = [];

            var s = $('statusChart'), vals = [stats.active, stats.maintenance, stats.unavailable], total = vals[0] + vals[1] + vals[2];
            if (s && total) charts.push(new Chart(s, {
                type: 'doughnut',
                data: { labels: STATUS_META.map(function (m) { return m.k; }),
                        datasets: [{ data: vals, backgroundColor: STATUS_META.map(function (m) { return m.c; }), borderWidth: 0, borderRadius: 10, spacing: 4, hoverOffset: 8 }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '74%', animation: { duration: 1100, easing: 'easeOutQuart' },
                    plugins: { legend: { display: false }, tooltip: Object.assign({}, tipBase, { usePointStyle: true,
                        callbacks: { label: function (c) { return '  ' + c.parsed + ' assets (' + (total ? (c.parsed / total * 100).toFixed(1) : 0) + '%)'; } } }) } }
            }));

            var c = $('categoryChart');
            if (c && categoryData.length) {
                var data = categoryData.map(function (d) { return d.value; }), sum = data.reduce(function (a, b) { return a + b; }, 0);
                charts.push(new Chart(c, {
                    type: 'bar',
                    data: { labels: categoryData.map(function (d) { return d.label; }),
                        datasets: [{ label: 'Assets', data: data,
                            backgroundColor: function (ctx) {
                                var base = CAT_COLORS[ctx.dataIndex % CAT_COLORS.length], a = ctx.chart.chartArea;
                                if (!a) return base;
                                var g = ctx.chart.ctx.createLinearGradient(0, a.top, 0, a.bottom);
                                g.addColorStop(0, base); g.addColorStop(1, base + '80'); return g;
                            },
                            hoverBackgroundColor: function (ctx) { return CAT_COLORS[ctx.dataIndex % CAT_COLORS.length]; },
                            borderRadius: { topLeft: 10, topRight: 10 }, borderSkipped: false, maxBarThickness: 56, barPercentage: .7, categoryPercentage: .7 }] },
                    options: { responsive: true, maintainAspectRatio: false, animation: { duration: 1000, easing: 'easeOutQuart' }, layout: { padding: { top: 24 } },
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0, color: C.tick, font: { size: 11, weight: '600' }, padding: 8 }, grid: { color: C.grid }, border: { display: false },
                                 title: { display: true, text: 'Number of assets', color: C.tick, font: { size: 11, weight: '600' } } },
                            x: { ticks: { color: C.tickX, font: { size: 12, weight: '600' }, padding: 6 }, grid: { display: false }, border: { display: false } }
                        },
                        plugins: { legend: { display: false }, tooltip: Object.assign({}, tipBase, { displayColors: false, callbacks: {
                            title: function (t) { return t[0].label; },
                            label: function (t) { return '  ' + t.parsed.y + ' assets · ' + (sum ? (t.parsed.y / sum * 100).toFixed(1) : 0) + '% of total'; } } }) } },
                    plugins: [{ id: 'barValueLabels', afterDatasetsDraw: function (chart) {
                        var ctx = chart.ctx; ctx.save();
                        ctx.font = '700 14px "Plus Jakarta Sans", sans-serif'; ctx.textAlign = 'center'; ctx.textBaseline = 'bottom'; ctx.fillStyle = C.tickX;
                        chart.getDatasetMeta(0).data.forEach(function (bar, i) { ctx.fillText(chart.data.datasets[0].data[i], bar.x, bar.y - 8); });
                        ctx.restore();
                    } }]
                }));
            }
        }
        render();
        document.addEventListener('ao:theme', render);
    }

    /* ---------- Count-up for the stat cards ---------- */
    if (!reduced) document.querySelectorAll('.stat-card [data-count]').forEach(function (el) {
        var to = +el.dataset.count, t0 = performance.now();
        if (!to) return;
        (function f(t) { var p = Math.min((t - t0) / 900, 1); el.textContent = Math.round(to * (1 - Math.pow(1 - p, 3))); if (p < 1) requestAnimationFrame(f); })(t0);
    });

    /* ---------- Recent Assets search ("/" focuses it, Esc clears, Enter searches everything) ---------- */
    var search = $('assetSearch'), rows = [].slice.call(document.querySelectorAll('#recentAssets tr[data-row]')), none = $('recentAssetsNone');
    if (search) {
        var filter = function () {
            var q = search.value.trim().toLowerCase(), shown = 0;
            rows.forEach(function (r) { var ok = !q || r.textContent.toLowerCase().indexOf(q) > -1; r.classList.toggle('d-none', !ok); if (ok) shown++; });
            if (none) { none.classList.toggle('d-none', shown > 0 || !rows.length); none.querySelector('span').textContent = search.value; }
        };
        search.addEventListener('input', filter);
        search.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { search.value = ''; filter(); search.blur(); }
            if (e.key === 'Enter' && search.value.trim()) location.href = @json(route('assets.index')) + '?search=' + encodeURIComponent(search.value.trim());
        });
    }

    /* ---------- Total Users chips filter the Recent Users list ---------- */
    var chips = document.querySelectorAll('.usr-chip'), users = [].slice.call(document.querySelectorAll('#recentUsers .ru-item')), current = '';
    function showUsers() {
        var n = 0;
        users.forEach(function (u) { var ok = (!current || u.dataset.status === current) && n < 5; u.classList.toggle('d-none', !ok); if (ok) n++; });
        chips.forEach(function (c) { c.classList.toggle('active', c.dataset.f === current); });
        var label = current === 'active' ? 'Active' : 'Deactivated';
        $('ruFilter').classList.toggle('show', !!current); $('ruFilterTxt').textContent = current ? label + ' only' : '';
        $('ruSub').textContent = current ? 'Latest ' + label.toLowerCase() + ' accounts' : 'Latest accounts added';
        $('ruEmpty').classList.toggle('d-none', n > 0);
    }
    if (chips.length) {
        chips.forEach(function (c) { c.addEventListener('click', function () { current = current === c.dataset.f ? '' : c.dataset.f; showUsers(); }); });
        $('ruClear').addEventListener('click', function () { current = ''; showUsers(); });
        showUsers();
    }

    /* ---------- Refresh ---------- */
    var refresh = $('refreshBtn');
    if (refresh) refresh.addEventListener('click', function () {
        $('refreshIcon').classList.add('btn-spin'); refresh.disabled = true;
        try { sessionStorage.setItem('assetone_refreshed', '1'); } catch (e) {}
        location.reload();
    });
    try { if (sessionStorage.getItem('assetone_refreshed')) { sessionStorage.removeItem('assetone_refreshed'); setTimeout(function () { if (window.aoToast) window.aoToast('Dashboard data synchronized!'); }, 400); } } catch (e) {}
})();
</script>
@endpush
