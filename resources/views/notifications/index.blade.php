@extends('layouts.app')

@section('title', 'Notifications')
@section('heading', 'Notifications')
@section('subheading', 'Your recent alerts and updates')

@section('content')
@php
    $query = request()->except('page');
    $link = fn (array $extra) => route('notifications.index', array_filter(array_merge($query, $extra), fn ($v) => $v !== null && $v !== ''));
    $hue = fn (string $s) => array_reduce(str_split($s ?: 'x'), fn ($h, $c) => ($h * 31 + ord($c)) % 360, 0);
    $initials = fn (string $n) => collect(preg_split('/\s+/', trim($n)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';
    $dayLabel = fn ($at) => $at->isToday() ? 'Today' : ($at->isYesterday() ? 'Yesterday' : $at->format('l, j F Y'));
    $lastDay = null;
@endphp

<x-banner title="Notifications" text="See who registered, added, updated or removed records in every module, and open each one for the full details.">
    @if($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.readAll') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-secondary"><i class="bi bi-check2-all me-2"></i>Mark all read</button>
        </form>
    @endif
    <a href="{{ route('notifications.export', $query) }}" class="btn btn-secondary"><i class="bi bi-download me-2"></i>Export CSV</a>
    @if($totalCount > 0)
        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#clearNotificationsModal"><i class="bi bi-trash3 me-2"></i>Clear all</button>
    @endif
</x-banner>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Total Activity</div><h2>{{ number_format($totalCount) }}</h2><div class="stat-sub">{{ $alertCount }} automated alerts</div></div>
        <div class="stat-icon icon-blue"><i class="bi bi-bell"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Unread</div><h2>{{ number_format($unreadCount) }}</h2><div class="stat-sub">{{ $unreadCount ? 'waiting for you' : 'all caught up' }}</div></div>
        <div class="stat-icon icon-red"><i class="bi bi-envelope"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Today</div><h2>{{ number_format($todayCount) }}</h2><div class="stat-sub">{{ $peopleToday }} people active</div></div>
        <div class="stat-icon icon-green"><i class="bi bi-calendar-day"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Last 7 Days</div><h2>{{ number_format($weekCount) }}</h2><div class="stat-sub">{{ $modulesThisWeek }} modules</div></div>
        <div class="stat-icon icon-orange"><i class="bi bi-calendar-week"></i></div>
    </div></div>
</div>

<div class="nt-wrap">
    <div class="content-card">
        <form method="GET" action="{{ route('notifications.index') }}" class="nt-tools" id="ntFilters">
            @if($module)<input type="hidden" name="module" value="{{ $module }}">@endif
            @if($unreadOnly)<input type="hidden" name="unread" value="1">@endif
            <div class="search-box"><i class="bi bi-search"></i><input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by person, record or action..." aria-label="Search activity"></div>
            <select name="period" class="form-select" aria-label="Period" onchange="this.form.requestSubmit()">
                <option value="">All time</option>
                @foreach($periods as $value => $label)
                    <option value="{{ $value }}" @selected($period === (string) $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="action" class="form-select" aria-label="Action" onchange="this.form.requestSubmit()">
                <option value="">All actions</option>
                @foreach($actions as $value => [$label])
                    <option value="{{ $value }}" @selected($action === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <span class="live-inline" data-live><a href="{{ $link(['unread' => $unreadOnly ? null : 1]) }}" class="chip {{ $unreadOnly ? 'active' : '' }}">Unread only</a></span>
        </form>

        <div class="chips mb-2" data-live>
            <a href="{{ $link(['module' => null]) }}" class="chip {{ $module === '' ? 'active' : '' }}">All<b>{{ $totalCount }}</b></a>
            @foreach($modules as $name => $count)
                <a href="{{ $link(['module' => $name]) }}" class="chip {{ $module === $name ? 'active' : '' }}">{{ $name }}<b>{{ $count }}</b></a>
            @endforeach
        </div>

        <div id="nItems" data-live>
            @forelse($notifications as $n)
                @php $day = $dayLabel($n->at); @endphp
                @if($day !== $lastDay)
                    @php $lastDay = $day; @endphp
                    <div class="nt-day">{{ $day }}</div>
                @endif
                <div class="nt-item a-{{ $n->action }} {{ $n->read ? '' : 'unread' }} mb-2" tabindex="0" role="button" data-id="{{ $n->id }}"
                     data-item="{{ json_encode([
                        'title' => $n->title, 'message' => $n->message, 'module' => $n->module,
                        'action' => $actions[$n->action][0] ?? ucfirst($n->action), 'icon' => $actions[$n->action][1] ?? 'bi-activity', 'cls' => 'a-'.$n->action,
                        'actor' => $n->actor ?: 'System', 'role' => $n->actorRole, 'initials' => $initials($n->actor ?: 'System'), 'hue' => $hue($n->actor ?: 'System'),
                        'when' => $n->at->format('D, j M Y, H:i'), 'ago' => $n->at->diffForHumans(), 'read' => $n->read, 'changes' => $n->changes,
                        'open' => route('notifications.read', $n->id), 'toggle' => route('notifications.toggle', $n->id), 'destroy' => route('notifications.destroy', $n->id),
                     ]) }}">
                    <div class="nt-ic"><i class="bi {{ $actions[$n->action][1] ?? 'bi-activity' }}"></i></div>
                    <div class="nt-main">
                        <span class="nt-title">@unless($n->read)<span class="badge bg-primary me-1">New</span>@endunless{{ $n->title }}</span>
                        <div class="nt-sub">{{ $n->message }}</div>
                        <div class="nt-meta">
                            <span class="pill">{{ $n->module }}</span>
                            <span class="pill">{{ $actions[$n->action][0] ?? ucfirst($n->action) }}</span>
                            @if($n->actor)<span><i class="bi bi-person me-1"></i>{{ $n->actor }}</span>@endif
                            <span title="{{ $n->at->format('d M Y, g:i A') }}"><i class="bi bi-clock me-1"></i>{{ $n->at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="nt-act">
                        <form method="POST" action="{{ route('notifications.toggle', $n->id) }}" class="m-0">
                            @csrf
                            <button type="submit" title="{{ $n->read ? 'Mark as unread' : 'Mark as read' }}" aria-label="{{ $n->read ? 'Mark as unread' : 'Mark as read' }}"><i class="bi {{ $n->read ? 'bi-envelope' : 'bi-envelope-open' }}"></i></button>
                        </form>
                        <form method="POST" action="{{ route('notifications.destroy', $n->id) }}" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="del" title="Delete notification" aria-label="Delete notification"><i class="bi bi-x-lg"></i></button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="nt-empty">
                    @if($totalCount)
                        <i class="bi bi-funnel"></i><h6>Nothing matches these filters</h6><span>Try a different module, period or search.</span>
                    @else
                        <i class="bi bi-bell-slash"></i><h6>No activity yet</h6><span>Notifications appear here as people register, add or update records in each module.</span>
                    @endif
                </div>
            @endforelse
        </div>

        <div class="mt-3" data-live>{{ $notifications->links() }}</div>
    </div>

    <div class="content-card nt-detail" id="ntDetail" aria-live="polite">
        <div class="nd-empty"><i class="bi bi-card-text"></i><h6>Select a notification</h6><span>Click any item on the left to see who did it, when, and exactly what changed.</span></div>
    </div>
</div>

<form method="POST" id="ntActionForm" class="d-none">
    @csrf
    <input type="hidden" name="_method" id="ntActionMethod" value="POST">
</form>

<div class="modal fade" id="clearNotificationsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-trash3 me-2"></i>Clear all notifications?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                This permanently removes all {{ number_format($totalCount) }} of your notifications, read and unread. New activity will be recorded again as people use AssetOne.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('notifications.clear') }}" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash3 me-2"></i>Yes, clear all</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// The "inside" view: who did it, when, and what changed.
(function () {
    var list = document.getElementById('nItems'), box = document.getElementById('ntDetail'),
        form = document.getElementById('ntActionForm'), method = document.getElementById('ntActionMethod');
    var items = [], sel = -1;
    var el = function (tag, cls, text) { var n = document.createElement(tag); if (cls) n.className = cls; if (text !== undefined) n.textContent = text; return n; };

    function post(url, verb) { form.action = url; method.value = verb || 'POST'; form.submit(); }

    function show(i, open) {
        if (i < 0 || i >= items.length) return;
        sel = i;
        var d = JSON.parse(items[i].dataset.item);
        items.forEach(function (it, j) { it.classList.toggle('active', j === i); });

        var body = el('div', 'nd-body ' + d.cls);
        var head = el('div', 'nd-head');
        var ic = el('div', 'nt-ic'); ic.innerHTML = '<i class="bi ' + d.icon + '"></i>';
        var ht = el('div'); ht.appendChild(el('h5', '', d.title));
        var meta = el('div', 'nt-meta'); meta.appendChild(el('span', 'pill', d.module)); meta.appendChild(el('span', 'pill', d.action)); ht.appendChild(meta);
        var nav = el('div', 'nd-nav');
        nav.innerHTML = '<button type="button" id="ndPrev" aria-label="Previous"><i class="bi bi-chevron-up"></i></button><button type="button" id="ndNext" aria-label="Next"><i class="bi bi-chevron-down"></i></button><button type="button" class="nd-close" id="ndClose" aria-label="Close"><i class="bi bi-x-lg"></i></button>';
        head.appendChild(ic); head.appendChild(ht); head.appendChild(nav); body.appendChild(head);

        var grid = el('div', 'nd-grid');
        var who = el('div'); who.appendChild(el('small', '', 'Performed by'));
        var whoRow = el('div', 'nd-who'), av = el('span', 'avatar-sm', d.initials); av.style.setProperty('--h', d.hue);
        var whoTxt = el('div'); whoTxt.appendChild(el('div', 'v', d.actor)); if (d.role) whoTxt.appendChild(el('small', '', d.role));
        whoRow.appendChild(av); whoRow.appendChild(whoTxt); who.appendChild(whoRow);
        var when = el('div'); when.appendChild(el('small', '', 'When')); when.appendChild(el('div', 'v', d.when)); when.appendChild(el('small', '', d.ago));
        grid.appendChild(who); grid.appendChild(when); body.appendChild(grid);

        if (d.message) body.appendChild(el('div', 'nd-msg ' + d.cls, d.message));

        if (d.changes && d.changes.length) {
            body.appendChild(el('div', 'nd-sec', 'What changed'));
            var table = el('table', 'nd-diff');
            table.innerHTML = '<thead><tr><th>Field</th><th>Before</th><th>After</th></tr></thead>';
            var tb = el('tbody');
            d.changes.forEach(function (c) {
                var tr = el('tr'); tr.appendChild(el('td', '', c.field)); tr.appendChild(el('td', 'from', c.from)); tr.appendChild(el('td', 'to', c.to)); tb.appendChild(tr);
            });
            table.appendChild(tb); body.appendChild(table);
        }

        var acts = el('div', 'nd-actions');
        acts.innerHTML = '<button type="button" class="btn btn-primary" id="ndOpen"><i class="bi bi-box-arrow-up-right me-2"></i>Open record</button>'
            + '<button type="button" class="btn btn-secondary" id="ndRead"><i class="bi ' + (d.read ? 'bi-envelope' : 'bi-envelope-open') + ' me-2"></i>' + (d.read ? 'Mark as unread' : 'Mark as read') + '</button>'
            + '<button type="button" class="btn btn-outline-danger" id="ndDel"><i class="bi bi-trash3 me-2"></i>Delete</button>';
        body.appendChild(acts);

        box.innerHTML = ''; box.appendChild(body);
        document.getElementById('ndPrev').disabled = i === 0;
        document.getElementById('ndNext').disabled = i === items.length - 1;
        document.getElementById('ndPrev').onclick = function () { show(sel - 1); };
        document.getElementById('ndNext').onclick = function () { show(sel + 1); };
        document.getElementById('ndClose').onclick = function () { box.classList.remove('open'); };
        document.getElementById('ndOpen').onclick = function () { post(d.open, 'POST'); };
        document.getElementById('ndRead').onclick = function () { post(d.toggle, 'POST'); };
        document.getElementById('ndDel').onclick = function () { post(d.destroy, 'DELETE'); };

        if (open && matchMedia('(max-width:1199.98px)').matches) box.classList.add('open');
        items[i].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }

    // The list is refreshed in place by the filters, so the items are looked up again each time.
    function collect() { items = [].slice.call(list.querySelectorAll('.nt-item')); sel = -1; }
    collect();
    document.addEventListener('ao:live', collect);
    list.addEventListener('click', function (e) {
        var it = e.target.closest('.nt-item'); if (!it || e.target.closest('.nt-act')) return;
        show(items.indexOf(it), true);
    });
    list.addEventListener('keydown', function (e) {
        if ((e.key !== 'Enter' && e.key !== ' ') || !e.target.classList.contains('nt-item')) return;
        e.preventDefault(); show(items.indexOf(e.target), true);
    });
})();
</script>
@endpush
