@extends('layouts.app')

@section('title', 'Asset Assignment')
@section('heading', 'Asset Assignment')
@section('subheading', 'Assign assets to users and track custody')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Asset Assignment</h3>
        <p class="text-muted mb-0">Assign an asset to a user, set when it is due back and record its return.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAssignmentModal">
        <i class="bi bi-plus-circle me-2"></i>New Assignment
    </button>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Pending Verification</div><h2>{{ number_format($pendingCount) }}</h2></div>
        <div class="stat-icon icon-orange"><i class="bi bi-hourglass-split"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Assigned</div><h2>{{ number_format($assignedCount) }}</h2></div>
        <div class="stat-icon icon-green"><i class="bi bi-person-check"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Overdue</div><h2>{{ number_format($overdueCount) }}</h2></div>
        <div class="stat-icon icon-red"><i class="bi bi-alarm"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Returned (History)</div><h2>{{ number_format($returnedCount) }}</h2></div>
        <div class="stat-icon icon-blue"><i class="bi bi-arrow-counterclockwise"></i></div>
    </div></div>
</div>

<div class="card content-card p-4">
    <form method="GET" action="{{ route('assignments.index') }}" class="row g-3 mb-4 align-items-end">
        <div class="col-md-7">
            <label class="form-label fw-semibold">Search</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search asset, PIC, code...">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="pending_verification" @selected($status === 'pending_verification')>Pending Verification</option>
                <option value="assigned" @selected($status === 'assigned')>Assigned</option>
                <option value="overdue" @selected($status === 'overdue')>Overdue</option>
                <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                <option value="unassigned" @selected($status === 'unassigned')>Returned</option>
            </select>
        </div>
        <div class="col-md-2">
            <a href="{{ route('assignments.index') }}" class="btn btn-outline-secondary w-100" title="Reset Filters"><i class="bi bi-arrow-clockwise me-1"></i>Reset</a>
        </div>
    </form>

    @if($overdueCount > 0)
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div><strong>{{ $overdueCount }}</strong> asset{{ $overdueCount > 1 ? 's are' : ' is' }} overdue for return.
                @if($status !== 'overdue')<a href="{{ route('assignments.index', ['status' => 'overdue']) }}" class="alert-link ms-1">Show overdue</a>@endif
            </div>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Asset Code</th>
                    <th>Asset Name</th>
                    <th>PIC (Person In Charge)</th>
                    <th>Assigned Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $i => $assignment)
                    <tr>
                        <td>{{ $assignments->firstItem() + $i }}</td>
                        <td class="fw-semibold">
                            @if($assignment->asset)
                                <a href="{{ route('assets.show', $assignment->asset) }}">{{ $assignment->asset->asset_code }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-dark">{{ $assignment->asset->name ?? '—' }}</td>
                        <td>{{ $assignment->custodian->name ?? '—' }}<div class="small text-muted">{{ $assignment->department }}</div></td>
                        <td>{{ $assignment->assigned_date->format('d M Y') }}</td>
                        <td>
                            @if($assignment->status === 'unassigned' && $assignment->returned_date)
                                <span class="text-muted">Returned {{ $assignment->returned_date->format('d M Y') }}</span>
                            @elseif($assignment->due_date)
                                <span class="{{ $assignment->isOverdue() ? 'text-danger fw-semibold' : '' }}">{{ $assignment->due_date->format('d M Y') }}</span>
                                @if($assignment->isOverdue())
                                    <div class="small text-danger">{{ (int) $assignment->due_date->diffInDays(today()) }} day(s) late</div>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $assignment->statusColor() }}">{{ $assignment->statusLabel() }}</span>
                            @if($assignment->status === 'rejected' && $assignment->rejection_reason)
                                <i class="bi bi-info-circle text-muted ms-1" title="{{ $assignment->rejection_reason }}"></i>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            @if($assignment->status === 'assigned')
                                <button type="button" class="btn btn-sm btn-outline-success me-1" data-bs-toggle="modal" data-bs-target="#returnAssignmentModal{{ $assignment->id }}" title="Return asset"><i class="bi bi-arrow-counterclockwise"></i></button>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#historyModal{{ $assignment->id }}" title="Assignment history"><i class="bi bi-clock-history"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editAssignmentModal{{ $assignment->id }}" title="Edit"><i class="bi bi-pencil"></i></button>
                            <form method="POST" action="{{ route('assignments.destroy', $assignment) }}" class="d-inline" onsubmit="return confirm('Delete this assignment?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <h5 class="mt-3">No Assignment Records</h5>
                            <p class="text-muted">Try adjusting your search or filters.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
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
                        <div class="mb-3">
                            <label class="form-label fw-medium">Return Date <span class="required">*</span></label>
                            <input type="date" name="returned_date" value="{{ now()->format('Y-m-d') }}" min="{{ $assignment->assigned_date->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" class="form-control" required>
                        </div>
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

{{-- New --}}
<div class="modal fade" id="addAssignmentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">New Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('assignments.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Asset <span class="required">*</span></label>
                        <select name="asset_id" class="form-select" required>
                            <option value="" selected disabled>Select asset</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}">{{ $asset->asset_code }} - {{ $asset->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Assign To (PIC) <span class="required">*</span></label>
                        <select name="custodian_id" class="form-select" required>
                            <option value="" selected disabled>Select user</option>
                            @foreach($custodians as $custodian)
                                <option value="{{ $custodian->id }}">{{ $custodian->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @include('assignments._loan-fields', ['assignment' => null, 'required' => true])
                    <div class="mb-3">
                        <label class="form-label fw-medium">Note / Reason</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="alert alert-info py-2 px-3 small mb-0">
                        <i class="bi bi-info-circle me-1"></i>The assignment will be marked <strong>Pending Verification</strong> and the staff member will be notified to accept or reject it.
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
// Loan duration: presets or a custom number of days, with the due date worked out live.
document.querySelectorAll('[data-loan]').forEach(function (box) {
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
});
</script>
@endpush
