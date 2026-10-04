@extends('layouts.app')

@section('title', 'Asset Maintenance')
@section('heading', 'Asset Maintenance')
@section('subheading', 'Record and manage maintenance activities for assets')

@php
    $statusBadges = ['pending' => 'warning', 'in_progress' => 'info', 'completed' => 'success', 'cancelled' => 'secondary'];
@endphp

@section('content')

<x-banner title="Asset Maintenance" text="Record and manage maintenance activities for assets." :keys="['N' => 'new', 'B' => 'board', '/' => 'search']">
    <button class="btn btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#addMaintenanceModal" data-key="n">
        <i class="bi bi-plus-circle me-2"></i>Add Maintenance
    </button>
</x-banner>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><a href="{{ route('maintenance.index') }}" class="stat-card d-flex justify-content-between align-items-start text-decoration-none">
        <div><div class="text-muted small text-uppercase">Total Maintenance</div><h2>{{ number_format($totalCount) }}</h2><div class="stat-sub">across {{ number_format($assetsServiced) }} assets</div></div>
        <div class="stat-icon icon-blue"><i class="bi bi-tools"></i></div>
    </a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('maintenance.index', ['status' => 'pending']) }}" class="stat-card d-flex justify-content-between align-items-start text-decoration-none">
        <div><div class="text-muted small text-uppercase">Pending</div><h2>{{ number_format($pendingCount) }}</h2><div class="stat-sub">waiting to start</div></div>
        <div class="stat-icon icon-orange"><i class="bi bi-clock"></i></div>
    </a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('maintenance.index', ['status' => 'in_progress']) }}" class="stat-card d-flex justify-content-between align-items-start text-decoration-none">
        <div><div class="text-muted small text-uppercase">In Progress</div><h2>{{ number_format($inProgressCount) }}</h2><div class="stat-sub">being serviced now</div></div>
        <div class="stat-icon icon-green"><i class="bi bi-arrow-repeat"></i></div>
    </a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('maintenance.index', ['status' => 'overdue']) }}" class="stat-card d-flex justify-content-between align-items-start text-decoration-none">
        <div><div class="text-muted small text-uppercase">Overdue</div><h2>{{ number_format($overdueCount) }}</h2><div class="stat-sub">{{ $overdueCount ? 'need attention' : 'all on schedule' }}</div></div>
        <div class="stat-icon icon-red"><i class="bi bi-alarm"></i></div>
    </a></div>
</div>

<div class="content-card mb-4">
    <div class="mb-3"><h5 class="fw-bold mb-0">Upcoming Schedule</h5><small class="text-muted">Next maintenance dates, nearest first. Click a card for details.</small></div>
    <div class="up-strip">
        @forelse($upcoming as $item)
            @php [$dueLabel, $dueColor] = $item->dueStatus(); @endphp
            <button type="button" class="up-card up-{{ $dueColor }}" data-bs-toggle="modal" data-bs-target="#upcomingModal{{ $item->id }}">
                <div class="up-date"><b>{{ $item->next_maintenance_date->format('j') }}</b><small>{{ $item->next_maintenance_date->format('M') }}</small></div>
                <div class="up-info">
                    <b>{{ $item->asset->name ?? 'Asset' }}</b>
                    <small>{{ $types[$item->type] ?? $item->type }}</small>
                    <span class="rel">{{ $item->dueRelative() }}</span>
                </div>
            </button>
        @empty
            <div class="text-muted small p-2">No upcoming maintenance scheduled.</div>
        @endforelse
    </div>
</div>

@php
    $keep = array_filter(['search' => $search, 'type' => $type, 'per_page' => $perPage !== 10 ? $perPage : null, 'sort' => $sort ?: null, 'dir' => $sort ? $dir : null]);
    $view = fn (array $extra) => route('maintenance.index', array_filter(array_merge($keep, $extra)));
@endphp
<div class="card content-card p-4" id="recordsCard">
    <form method="GET" action="{{ route('maintenance.index') }}" class="row g-3 mb-3" id="mtFilters">
        @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        @if($sort)<input type="hidden" name="sort" value="{{ $sort }}"><input type="hidden" name="dir" value="{{ $dir }}">@endif
        <div class="col-12 col-md-7">
            <label class="form-label fw-semibold">Search</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search maintenance ID, asset or type...">
            </div>
        </div>
        <div class="col-12 col-md-5">
            <label class="form-label fw-semibold">Maintenance Type</label>
            <select name="type" class="form-select" onchange="this.form.submit()">
                <option value="">All Maintenance Types</option>
                @foreach($types as $value => $label)
                    <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </form>

    {{-- Status chips, rows per page, table / board switch, export --}}
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <div class="chips mb-0">
            @foreach($chips as $value => [$label, $count])
                <a href="{{ $view(['status' => $value ?: null]) }}" class="chip {{ $value === 'overdue' ? 'chip-overdue' : '' }} {{ (string) $status === (string) $value ? 'active' : '' }}">{{ $label }}<b>{{ $count }}</b></a>
            @endforeach
        </div>
        <div class="ms-auto d-flex flex-wrap gap-2 align-items-center">
            <select class="form-select" style="width:auto;min-height:40px" aria-label="Rows per page" onchange="var f=document.getElementById('mtFilters');f.per_page.value=this.value;f.submit()">
                @foreach([5, 10, 25, 50] as $size)
                    <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }} rows</option>
                @endforeach
            </select>
            <div class="seg" id="viewSeg">
                <button type="button" data-view="table" title="Table view"><i class="bi bi-list-ul"></i></button>
                <button type="button" data-view="board" title="Board view" data-key="b"><i class="bi bi-kanban"></i></button>
            </div>
            <a href="{{ request()->fullUrlWithQuery(['export' => 1, 'page' => null]) }}" class="btn btn-secondary"><i class="bi bi-download me-2"></i>Export CSV</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 text-nowrap" data-sortable data-server-sort data-sort="{{ $sort }}" data-dir="{{ $dir }}">
            <thead>
                <tr>
                    <th>No.</th><th>Picture</th><th data-sort-key="code">Maintenance ID</th><th data-sort-key="asset">Asset ID</th><th data-sort-key="name">Asset Name</th><th data-sort-key="type">Maintenance Type</th>
                    <th data-sort-key="date">Maintenance Date</th><th data-sort-key="next">Next Maintenance</th><th data-sort-key="status">Status</th><th>Due Status</th><th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($maintenances as $i => $record)
                    @php
                        [$dueLabel, $dueColor] = $record->dueStatus();
                        $open = ! in_array($record->status, ['completed', 'cancelled'], true);
                    @endphp
                    <tr>
                        <td>{{ $maintenances->firstItem() + $i }}</td>
                        <td>@include('partials.thumb', ['url' => $record->asset?->photoUrl(), 'alt' => $record->asset->name ?? 'Asset', 'info' => ($record->asset->asset_code ?? '').' · '.($types[$record->type] ?? $record->type)])</td>
                        <td class="fw-semibold">{{ $record->maintenance_code }}</td>
                        <td>{{ $record->asset->asset_code ?? '—' }}</td>
                        <td class="text-dark">{{ $record->asset->name ?? '—' }}</td>
                        <td>{{ $types[$record->type] ?? $record->type }}</td>
                        <td data-sort="{{ $record->maintenance_date->format('Ymd') }}">{{ $record->maintenance_date->format('d M Y') }}</td>
                        <td data-sort="{{ optional($record->next_maintenance_date)->format('Ymd') ?? '99999999' }}">
                            {{ optional($record->next_maintenance_date)->format('d M Y') ?? '—' }}
                            @if($record->next_maintenance_date && $open)
                                <small class="d-block {{ $dueLabel === 'Overdue' ? 'text-danger' : 'text-muted' }}">{{ strtolower($record->dueRelative()) }}</small>
                            @endif
                        </td>
                        <td><span class="badge bg-{{ $statusBadges[$record->status] ?? 'secondary' }}">{{ $statuses[$record->status] ?? $record->status }}</span></td>
                        <td><span class="badge bg-{{ $dueColor }}">{{ $dueLabel }}</span></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#viewMaintenanceModal{{ $record->id }}" title="Details"><i class="bi bi-eye"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editMaintenanceModal{{ $record->id }}" title="Edit"><i class="bi bi-pencil"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteMaintenanceModal{{ $record->id }}" title="Delete"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11">
                        <div class="empty-state">
                            <i class="bi bi-inboxes"></i>
                            <h6>No maintenance records found</h6>
                            <span>Try clearing the search or filters.</span>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Board view: the same records, grouped by status. Drag a card to change its status. --}}
    <div class="board" id="board" data-url="{{ route('maintenance.status', '__ID__') }}">
        @foreach($statuses as $value => $label)
            @continue($status && $status !== 'overdue' && $status !== $value)
            @php $cards = $maintenances->getCollection()->where('status', $value); @endphp
            <div class="kb-col" data-status="{{ $value }}" data-label="{{ $label }}">
                <div class="kb-head"><span><i class="kdot" style="background:{{ ['pending' => '#f59e0b', 'in_progress' => '#06b6d4', 'completed' => '#10b981', 'cancelled' => '#94a3b8'][$value] }}"></i>{{ $label }}</span><b>{{ $cards->count() }}</b></div>
                <div class="kb-list">
                    @forelse($cards as $record)
                        @php
                            [$dueLabel, $dueColor] = $record->dueStatus();
                            $order = array_keys($statuses); $pos = array_search($value, $order, true);
                        @endphp
                        <div class="kb-card g" draggable="true" data-id="{{ $record->id }}" data-code="{{ $record->maintenance_code }}" data-modal="#viewMaintenanceModal{{ $record->id }}">
                            <div class="d-flex gap-2 align-items-center">
                                @include('partials.thumb', ['url' => $record->asset?->photoUrl(), 'alt' => $record->asset->name ?? 'Asset'])
                                <div style="min-width:0"><b>{{ $record->asset->name ?? '—' }}</b><small>{{ $record->asset->asset_code ?? '—' }} · {{ $record->maintenance_code }}</small></div>
                            </div>
                            <div class="kb-meta">
                                <span class="pill">{{ $types[$record->type] ?? $record->type }}</span>
                                <span class="pill"><i class="bi bi-calendar-event"></i>{{ $record->maintenance_date->format('d M Y') }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="badge bg-{{ $dueColor }}">{{ $dueLabel }}</span>
                                <span class="kb-move">
                                    @if($pos > 0)<button type="button" data-move="{{ $order[$pos - 1] }}" title="Move back">&lsaquo;</button>@endif
                                    @if($pos < count($order) - 1)<button type="button" data-move="{{ $order[$pos + 1] }}" title="Move forward">&rsaquo;</button>@endif
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="kb-empty">Drop a card here</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2 pager">
        <small class="text-muted">Showing {{ $maintenances->firstItem() ?? 0 }} to {{ $maintenances->lastItem() ?? 0 }} of {{ $maintenances->total() }} records</small>
        {{ $maintenances->links() }}
    </div>
</div>

@foreach($maintenances as $record)
    @include('maintenance._details', ['record' => $record, 'modalId' => 'viewMaintenanceModal'.$record->id])

    <div class="modal fade" id="editMaintenanceModal{{ $record->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Maintenance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('maintenance.update', $record) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        @include('maintenance._fields', ['record' => $record])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteMaintenanceModal{{ $record->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Delete Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Delete maintenance record <strong>{{ $record->maintenance_code }}</strong> for {{ $record->asset->name ?? 'this asset' }}? This cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('maintenance.destroy', $record) }}" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i>Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@foreach($upcoming as $item)
    @include('maintenance._details', ['record' => $item, 'modalId' => 'upcomingModal'.$item->id])
@endforeach

<div class="modal fade" id="addMaintenanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add Maintenance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('maintenance.store') }}">
                @csrf
                <div class="modal-body">
                    @include('maintenance._fields', ['record' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Table / board switch (remembered) and drag-and-drop status changes on the board.
(function () {
    var card = document.getElementById('recordsCard'), board = document.getElementById('board'), seg = document.getElementById('viewSeg');
    var KEY = 'assetone_mt_view', view = 'table';
    try { view = localStorage.getItem(KEY) || 'table'; } catch (e) {}
    function apply() {
        card.classList.toggle('view-board', view === 'board');
        seg.querySelectorAll('button').forEach(function (b) { b.classList.toggle('active', b.dataset.view === view); });
        // "B" toggles: point the shortcut at whichever view is not showing.
        seg.querySelectorAll('button').forEach(function (b) { if (b.dataset.view === (view === 'board' ? 'table' : 'board')) b.setAttribute('data-key', 'b'); else b.removeAttribute('data-key'); });
    }
    seg.addEventListener('click', function (e) {
        var b = e.target.closest('button'); if (!b) return;
        view = b.dataset.view; try { localStorage.setItem(KEY, view); } catch (err) {} apply();
    });
    apply();

    var token = document.querySelector('#mtFilters input[name=_token]') || document.querySelector('input[name=_token]');
    function moveTo(id, status) {
        var el = board.querySelector('.kb-card[data-id="' + id + '"]');
        if (!el || el.closest('.kb-col').dataset.status === status) return;
        fetch(board.dataset.url.replace('__ID__', id), {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token ? token.value : '' },
            body: JSON.stringify({ status: status })
        }).then(function (r) { return r.ok ? r.json() : Promise.reject(); })
          .then(function (d) { if (window.aoToast) window.aoToast(d.message); setTimeout(function () { location.reload(); }, 700); })
          .catch(function () { if (window.aoToast) window.aoToast('Could not update the status. Please try again.'); });
    }

    var dragId = null;
    board.addEventListener('dragstart', function (e) {
        var c = e.target.closest('.kb-card'); if (!c) return;
        dragId = c.dataset.id; c.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', dragId);
    });
    board.addEventListener('dragend', function () { board.querySelectorAll('.dragging,.over').forEach(function (x) { x.classList.remove('dragging', 'over'); }); });
    board.addEventListener('dragover', function (e) {
        var col = e.target.closest('.kb-col'); if (!col) return;
        e.preventDefault();
        board.querySelectorAll('.over').forEach(function (x) { if (x !== col) x.classList.remove('over'); });
        col.classList.add('over');
    });
    board.addEventListener('drop', function (e) {
        var col = e.target.closest('.kb-col'); if (!col) return;
        e.preventDefault(); col.classList.remove('over');
        moveTo(dragId || e.dataTransfer.getData('text/plain'), col.dataset.status);
    });
    board.addEventListener('click', function (e) {
        var c = e.target.closest('.kb-card'); if (!c) return;
        if (e.target.closest('[data-lightbox]')) return;
        var mv = e.target.closest('[data-move]');
        if (mv) { moveTo(c.dataset.id, mv.dataset.move); return; }
        bootstrap.Modal.getOrCreateInstance(document.querySelector(c.dataset.modal)).show();
    });
})();
</script>
@endpush
