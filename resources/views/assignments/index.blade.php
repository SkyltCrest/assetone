@extends('layouts.app')

@section('title', 'Asset Assignment')
@section('heading', 'Asset Assignment')
@section('subheading', 'Assign assets to users and track custody')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Asset Assignment</h3>
        <p class="text-muted mb-0">Assign an asset to a user and track its status.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAssignmentModal">
        <i class="bi bi-plus-circle me-2"></i>New Assignment
    </button>
</div>

<div class="row g-4 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <p class="text-muted small text-uppercase fw-medium mb-1">Total</p>
            <h3 class="fw-bold mb-0">{{ number_format($totalCount) }}</h3>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <p class="text-muted small text-uppercase fw-medium mb-1">Pending Verification</p>
            <h3 class="fw-bold mb-0 text-warning">{{ number_format($pendingCount) }}</h3>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <p class="text-muted small text-uppercase fw-medium mb-1">Assigned</p>
            <h3 class="fw-bold mb-0 text-success">{{ number_format($assignedCount) }}</h3>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <p class="text-muted small text-uppercase fw-medium mb-1">Rejected</p>
            <h3 class="fw-bold mb-0 text-danger">{{ number_format($rejectedCount) }}</h3>
        </div>
    </div>
</div>

<div class="card content-card p-4">
    <form method="GET" action="{{ route('assignments.index') }}" class="row g-3 mb-4 align-items-end">
        <div class="col-md-8">
            <label class="form-label fw-semibold">Search</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search asset or user...">
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="pending_verification" @selected($status === 'pending_verification')>Pending Verification</option>
                <option value="assigned" @selected($status === 'assigned')>Assigned</option>
                <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                <option value="unassigned" @selected($status === 'unassigned')>Returned</option>
            </select>
        </div>
        <div class="col-md-1">
            <a href="{{ route('assignments.index') }}" class="btn btn-outline-secondary w-100" title="Reset Filters"><i class="bi bi-arrow-clockwise"></i></a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Asset</th>
                    <th>Assigned To</th>
                    <th>Department</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $i => $assignment)
                    <tr>
                        <td>{{ $assignments->firstItem() + $i }}</td>
                        <td class="fw-semibold">{{ $assignment->asset->asset_code ?? '—' }} <span class="text-muted fw-normal">{{ $assignment->asset->name ?? '' }}</span></td>
                        <td>{{ $assignment->custodian->name ?? '—' }}</td>
                        <td>{{ $assignment->department }}</td>
                        <td>{{ $assignment->assigned_date->format('d M Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $assignment->statusColor() }}">{{ $assignment->statusLabel() }}</span>
                            @if($assignment->status === 'rejected' && $assignment->rejection_reason)
                                <i class="bi bi-info-circle text-muted ms-1" title="{{ $assignment->rejection_reason }}"></i>
                            @endif
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editAssignmentModal{{ $assignment->id }}" title="Edit"><i class="bi bi-pencil"></i></button>
                            <form method="POST" action="{{ route('assignments.destroy', $assignment) }}" class="d-inline" onsubmit="return confirm('Delete this assignment?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

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
                                            <label class="form-label fw-medium">Assign To <span class="required">*</span></label>
                                            <select name="custodian_id" class="form-select" required>
                                                @foreach($custodians as $custodian)
                                                    <option value="{{ $custodian->id }}" @selected($assignment->custodian_id === $custodian->id)>{{ $custodian->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-medium">Date <span class="required">*</span></label>
                                            <input type="date" name="assigned_date" value="{{ $assignment->assigned_date->format('Y-m-d') }}" class="form-control" required>
                                        </div>
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
                                            <label class="form-label fw-medium">Notes</label>
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
                @empty
                    <tr><td colspan="7">
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
                        <label class="form-label fw-medium">Assign To <span class="required">*</span></label>
                        <select name="custodian_id" class="form-select" required>
                            <option value="" selected disabled>Select user</option>
                            @foreach($custodians as $custodian)
                                <option value="{{ $custodian->id }}">{{ $custodian->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Date <span class="required">*</span></label>
                        <input type="date" name="assigned_date" value="{{ now()->format('Y-m-d') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Notes</label>
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
