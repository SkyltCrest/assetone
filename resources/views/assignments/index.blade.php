@extends('layouts.app')

@section('title', 'Asset Assignment')
@section('heading', 'Asset Assignment')
@section('subheading', 'Assign assets to users and track custody')

@section('content')

<x-banner title="Asset Assignment" text="Assign an asset to a user and track its status." :keys="['N' => 'new', '/' => 'search']">
    <button class="btn btn-primary text-nowrap" data-bs-toggle="modal" data-bs-target="#addAssignmentModal" data-key="n" data-assign-new>
        <i class="bi bi-plus-circle me-2"></i>New Assignment
    </button>
</x-banner>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Total Assets</div><h2>{{ number_format($assetTotal) }}</h2><div class="stat-sub">{{ number_format($pendingCount) }} awaiting verification</div></div>
        <div class="stat-icon icon-blue"><i class="bi bi-box-seam"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Available</div><h2>{{ number_format($availableCount) }}</h2><div class="stat-sub">ready to assign</div></div>
        <div class="stat-icon icon-green"><i class="bi bi-check-circle"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Assigned</div><h2>{{ number_format($assignedCount) }}</h2><div class="stat-sub">{{ $overdueCount ? number_format($overdueCount).' overdue' : 'none overdue' }}</div></div>
        <div class="stat-icon icon-orange"><i class="bi bi-person-check"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Returned (History)</div><h2>{{ number_format($returnedCount) }}</h2><div class="stat-sub">completed loans</div></div>
        <div class="stat-icon icon-red"><i class="bi bi-arrow-counterclockwise"></i></div>
    </div></div>
</div>

<div class="card content-card p-4">
    <form method="GET" action="{{ route('assignments.index') }}" class="row g-3 mb-3 align-items-end">
        @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <div class="col-md-10">
            <label class="form-label fw-semibold">Search</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search asset, PIC, code...">
            </div>
        </div>
        <div class="col-md-2">
            <a href="{{ route('assignments.index') }}" class="btn btn-outline-secondary w-100" title="Reset Filters"><i class="bi bi-arrow-clockwise me-1"></i>Reset</a>
        </div>
    </form>

    @if($overdueCount > 0)
        <div class="overdue-banner show">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span data-live>{{ $overdueCount }} asset(s) overdue for return. Please take action.
                @if($status !== 'overdue')<a href="{{ route('assignments.index', ['status' => 'overdue']) }}" class="alert-link ms-1">Show overdue</a>@endif
            </span>
        </div>
    @endif

    {{-- Status chips + export --}}
    <div class="chips mb-3" data-live>
        @foreach($chips as $value => [$label, $count])
            <a href="{{ route('assignments.index', array_filter(['status' => $value ?: null, 'search' => $search])) }}"
               class="chip {{ $value === 'overdue' ? 'chip-overdue' : '' }} {{ (string) $status === (string) $value ? 'active' : '' }}">{{ $label }}<b>{{ $count }}</b></a>
        @endforeach
        <div class="chip-tools">
            <a href="{{ request()->fullUrlWithQuery(['export' => 1, 'page' => null]) }}" class="btn btn-secondary"><i class="bi bi-download me-2"></i>Export CSV</a>
        </div>
    </div>

    <div class="table-responsive" data-live>
        <table class="table table-hover align-middle mb-0" data-sortable data-server-sort data-sort="{{ $sort }}" data-dir="{{ $dir }}">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Photo</th>
                    <th data-sort-key="code">Asset Code</th>
                    <th data-sort-key="name">Asset Name</th>
                    <th data-sort-key="pic">PIC (Person In Charge)</th>
                    <th data-sort-key="assigned">Assigned Date</th>
                    <th data-sort-key="due">Due Date</th>
                    <th data-sort-key="status">Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $i => $assignment)
                    @php
                        $asset = $assignment->asset;
                        $pic = $assignment->custodian;
                        $overdue = $assignment->isOverdue();
                        $daysLeft = ($assignment->status === 'assigned' && $assignment->due_date) ? (int) today()->diffInDays($assignment->due_date, false) : null;
                    @endphp
                    <tr class="{{ $overdue ? 'row-overdue' : '' }}">
                        <td>{{ $assignments->firstItem() + $i }}</td>
                        <td>@include('partials.thumb', ['url' => $asset?->photoUrl(), 'alt' => $asset->name ?? 'Asset', 'info' => ($asset->asset_code ?? '').' · '.$assignment->statusLabel().($pic ? ' · '.$pic->name : '')])</td>
                        <td class="fw-semibold">
                            @if($asset)
                                <a href="{{ route('assets.show', $asset) }}">{{ $asset->asset_code }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-dark">{{ $asset->name ?? '—' }}</td>
                        <td data-sort="{{ $pic->name ?? '' }}">
                            @if($pic)
                                <div class="pic-wrap">
                                    @include('partials.avatar', ['user' => $pic, 'size' => 34])
                                    <div class="pic-txt"><strong>{{ $pic->name }}</strong><small>{{ ucwords(str_replace('_', ' ', $pic->role)) }}{{ $pic->department ? ' · '.str_replace(' Department', '', $pic->department) : '' }}</small></div>
                                </div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td data-sort="{{ $assignment->assigned_date->format('Ymd') }}">{{ $assignment->assigned_date->format('d M Y') }}</td>
                        <td class="due-cell" data-sort="{{ optional($assignment->due_date)->format('Ymd') ?? '99999999' }}">
                            @if($assignment->status === 'unassigned' && $assignment->returned_date)
                                <span class="text-muted">Returned {{ $assignment->returned_date->format('d M Y') }}</span>
                            @elseif($assignment->due_date)
                                {{ $assignment->due_date->format('d M Y') }}
                                @if($overdue)
                                    <small class="due-warning"><i class="bi bi-exclamation-triangle-fill"></i> {{ abs($daysLeft) }} day(s) late</small>
                                @elseif($daysLeft !== null && $daysLeft <= 3)
                                    <small class="due-soon"><i class="bi bi-clock-fill"></i> {{ $daysLeft }} day(s) left</small>
                                @elseif($daysLeft !== null)
                                    <small class="due-ok">{{ $daysLeft }} day(s) left</small>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $overdue ? 'badge-overdue' : 'bg-'.$assignment->statusColor() }}">{{ $overdue ? 'Overdue' : $assignment->statusLabel() }}</span>
                            @if($assignment->status === 'rejected' && $assignment->rejection_reason)
                                <i class="bi bi-info-circle info-tip" data-bs-toggle="tooltip" title="{{ $assignment->rejection_reason }}"></i>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            @if($assignment->status === 'assigned' && $asset)
                                <button type="button" class="btn btn-sm btn-outline-warning me-1" title="Reassign" data-reassign="{{ $asset->id }}"><i class="bi bi-arrow-left-right"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-danger me-1" data-bs-toggle="modal" data-bs-target="#returnAssignmentModal{{ $assignment->id }}" title="Return"><i class="bi bi-box-arrow-in-left"></i></button>
                                @if($overdue)
                                    <form method="POST" action="{{ route('assignments.remind', $assignment) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger me-1" title="Send Reminder"><i class="bi bi-bell-fill"></i></button>
                                    </form>
                                @endif
                            @endif
                            <div class="dropdown d-inline-block">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" title="More actions" aria-label="More actions"><i class="bi bi-three-dots-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#historyModal{{ $assignment->id }}"><i class="bi bi-clock-history me-2"></i>History</button></li>
                                    <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editAssignmentModal{{ $assignment->id }}"><i class="bi bi-pencil me-2"></i>Edit</button></li>
                                    <li>
                                        <form method="POST" action="{{ route('assignments.destroy', $assignment) }}" class="m-0" data-confirm="Delete this assignment?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9">
                        <div class="empty-state">
                            <i class="bi bi-inboxes"></i>
                            <h6>No Assignment Records</h6>
                            <span>Try adjusting your search or filters.</span>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2" data-live>
        <small class="text-muted">Showing {{ $assignments->firstItem() ?? 0 }} to {{ $assignments->lastItem() ?? 0 }} of {{ $assignments->total() }} assignments</small>
        {{ $assignments->links() }}
    </div>
</div>

@foreach($assignments as $assignment)
    {{-- Edit --}}
    <div class="modal fade" id="editAssignmentModal{{ $assignment->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Assignment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('assignments.update', $assignment) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Asset <span class="required">*</span></label>
                            <select name="asset_id" class="form-select" required>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}" @selected($assignment->asset_id === $asset->id)>{{ $asset->asset_code }} - {{ $asset->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Assign To (PIC) <span class="required">*</span></label>
                            <select name="custodian_id" class="form-select" required>
                                @foreach($custodians as $custodian)
                                    <option value="{{ $custodian->id }}" @selected($assignment->custodian_id === $custodian->id)>{{ $custodian->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @include('assignments._loan-fields', ['assignment' => $assignment, 'required' => false])
                        <div class="mb-3">
                            <label class="form-label fw-medium">Status</label>
                            <select name="status" class="form-select">
                                <option value="pending_verification" @selected($assignment->status === 'pending_verification')>Pending Verification</option>
                                <option value="assigned" @selected($assignment->status === 'assigned')>Assigned</option>
                                <option value="rejected" @selected($assignment->status === 'rejected')>Rejected</option>
                                <option value="unassigned" @selected($assignment->status === 'unassigned')>Returned</option>
                            </select>
                            <small class="text-muted">Set to <em>Pending Verification</em> to send it back to {{ $assignment->custodian->name ?? 'the staff member' }} for re-checking.</small>
                        </div>
                        @if($assignment->status === 'rejected' && $assignment->rejection_reason)
                            <div class="alert alert-warning py-2 px-3 small mb-3">
                                <strong>Rejection reason:</strong> {{ $assignment->rejection_reason }}
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label fw-medium">Place of Use</label>
                            <input type="text" name="place_of_use" value="{{ $assignment->place_of_use }}" class="form-control" maxlength="255" placeholder="Where the asset will be used">
                            <div class="form-text">Application no. {{ $assignment->application_no ?: '-' }} &middot; printed on the KEW.PA-9 form.</div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label fw-medium">Note / Reason</label>
                            <textarea name="notes" class="form-control" rows="3">{{ $assignment->notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Return --}}
    @if($assignment->status === 'assigned')
    <div class="modal fade" id="returnAssignmentModal{{ $assignment->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Return Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('assignments.return', $assignment) }}">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-3">Confirm that <strong>{{ $assignment->asset->asset_code ?? '' }} — {{ $assignment->asset->name ?? '' }}</strong> has been returned by <strong>{{ $assignment->custodian->name ?? 'the custodian' }}</strong>.</p>
                        @if($assignment->isOverdue())
                            <div class="alert alert-danger py-2 px-3 small">This asset was due back on {{ $assignment->due_date->format('d M Y') }}.</div>
                        @endif
                        <div class="mb-1">
                            <label class="form-label fw-medium">Note</label>
                            <textarea name="return_note" class="form-control" rows="3" placeholder="Condition of the asset, anything missing..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-arrow-counterclockwise me-1"></i>Confirm Return</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- History of the asset --}}
    <div class="modal fade" id="historyModal{{ $assignment->id }}" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Assignment History — {{ $assignment->asset->asset_code ?? '' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">{{ $assignment->asset->name ?? '' }}</p>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead><tr><th>PIC</th><th>Role</th><th>Assigned</th><th>Due Date</th><th>Returned</th><th>Status</th><th>Note</th></tr></thead>
                            <tbody>
                                @foreach(($assignment->asset->assignments ?? collect())->sortByDesc('assigned_date') as $entry)
                                    <tr>
                                        <td class="text-dark">{{ $entry->custodian->name ?? '—' }}</td>
                                        <td>{{ $entry->custodian ? ucwords(str_replace('_', ' ', $entry->custodian->role)) : '—' }}</td>
                                        <td>{{ $entry->assigned_date->format('d M Y') }}</td>
                                        <td>{{ optional($entry->due_date)->format('d M Y') ?? '—' }}</td>
                                        <td>{{ optional($entry->returned_date)->format('d M Y') ?? '—' }}</td>
                                        <td><span class="badge bg-{{ $entry->statusColor() }}">{{ $entry->statusLabel() }}</span></td>
                                        <td class="small">{{ $entry->return_note ?: ($entry->notes ?: '—') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

{{-- New / Reassign --}}
<div class="modal fade" id="addAssignmentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="assignModalTitle">New Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('assignments.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Asset <span class="required">*</span></label>
                        <select name="asset_id" id="assignAsset" class="form-select" required>
                            <option value="" selected disabled>Select asset</option>
                            @foreach($assets as $asset)
                                @php $busy = in_array($asset->id, $busyAssetIds, true); @endphp
                                <option value="{{ $asset->id }}" data-available="{{ $busy ? 0 : 1 }}"
                                        data-photo="{{ $asset->photoUrl() }}" data-name="{{ $asset->name }}" data-code="{{ $asset->asset_code }}"
                                        @if($busy) hidden disabled @endif>{{ $asset->asset_code }} — {{ $asset->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text" id="assignAssetHint">Only <strong>available</strong> assets can be assigned.</div>
                        <div class="asg-prev d-none" id="asgPrev"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Assign To (PIC) <span class="required">*</span></label>
                        <select name="custodian_id" class="form-select" required>
                            <option value="" selected disabled>Select user</option>
                            @foreach($custodians as $custodian)
                                <option value="{{ $custodian->id }}">{{ $custodian->name }} — {{ ucwords(str_replace('_', ' ', $custodian->role)) }}{{ $custodian->department ? ' ('.$custodian->department.')' : '' }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Includes administrators, asset officers and department staff.</div>
                    </div>
                    @include('assignments._loan-fields', ['assignment' => null, 'required' => true])
                    <div class="mb-3">
                        <label class="form-label fw-medium">Place of Use</label>
                        <input type="text" name="place_of_use" class="form-control" maxlength="255" placeholder="Where the asset will be used">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Note / Reason</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional note..."></textarea>
                    </div>
                    <div class="alert alert-info py-2 px-3 small mb-0">
                        <i class="bi bi-info-circle me-1"></i>The assignment will be marked <strong>Pending Verification</strong> and the staff member will be notified to accept or reject it.
                        <span id="reassignNote" class="d-none">The current holder keeps the asset until the new person accepts; their record is then closed as returned.</span>
                    </div>
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
// New Assignment / Reassign: asset preview, and which assets may be picked.
(function () {
    var modal = document.getElementById('addAssignmentModal'), select = document.getElementById('assignAsset'),
        prev = document.getElementById('asgPrev'), title = document.getElementById('assignModalTitle'),
        hint = document.getElementById('assignAssetHint'), note = document.getElementById('reassignNote');
    var reassignId = null;

    function setOptions() {
        [].slice.call(select.options).forEach(function (o) {
            if (!o.value) return;
            var ok = o.dataset.available === '1' || o.value === reassignId;
            o.hidden = !ok; o.disabled = !ok;
        });
    }
    function preview() {
        var o = select.selectedOptions[0];
        prev.innerHTML = '';
        if (!o || !o.value) { prev.classList.add('d-none'); return; }
        prev.classList.remove('d-none');
        var pic;
        if (o.dataset.photo) { pic = document.createElement('img'); pic.src = o.dataset.photo; pic.alt = ''; }
        else { pic = document.createElement('span'); pic.className = 'thumb thumb-empty'; pic.innerHTML = '<i class="bi bi-image"></i>'; }
        var txt = document.createElement('div'), s = document.createElement('strong'), sm = document.createElement('small');
        s.textContent = o.dataset.name; sm.textContent = o.dataset.code; txt.appendChild(s); txt.appendChild(sm);
        prev.appendChild(pic); prev.appendChild(txt);
    }
    select.addEventListener('change', preview);
    document.querySelectorAll('[data-assign-new]').forEach(function (b) { b.addEventListener('click', function () { reassignId = null; }); });
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-reassign]'); if (!b) return;
        reassignId = b.dataset.reassign; bootstrap.Modal.getOrCreateInstance(modal).show();
    });
    modal.addEventListener('show.bs.modal', function () {
        setOptions();
        title.textContent = reassignId ? 'Reassign Asset' : 'New Assignment';
        hint.classList.toggle('d-none', !!reassignId); note.classList.toggle('d-none', !reassignId);
        select.value = reassignId || '';
        select.style.pointerEvents = reassignId ? 'none' : '';
        preview();
    });
})();
</script>
<script>
// Loan duration: presets or a custom number of days, with the due date worked out live.
function loanFields(root) { root.querySelectorAll('[data-loan]').forEach(function (box) {
    var start = box.querySelector('[data-loan-start]'),
        preset = box.querySelector('[data-loan-preset]'),
        customWrap = box.querySelector('[data-loan-custom-wrap]'),
        custom = box.querySelector('[data-loan-custom]'),
        due = box.querySelector('[data-loan-due]'),
        days = box.querySelector('[data-loan-days]');

    function update() {
        var isCustom = preset.value === 'custom';
        customWrap.classList.toggle('d-none', !isCustom);
        custom.required = isCustom;
        var n = isCustom ? parseInt(custom.value, 10) : parseInt(preset.value, 10);
        days.value = n > 0 ? n : '';
        if (n > 0 && start.value) {
            var d = new Date(start.value + 'T00:00:00');
            d.setDate(d.getDate() + n);
            due.value = d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
        } else {
            due.value = '';
        }
    }

    [start, preset, custom].forEach(function (el) { el.addEventListener('input', update); el.addEventListener('change', update); });
    update();
}); }
loanFields(document);
document.addEventListener('ao:live', function (e) { e.detail.modals.forEach(loanFields); });
</script>
@endpush
