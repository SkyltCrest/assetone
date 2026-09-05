@extends('layouts.app')

@section('title', 'Verify Assignment')
@section('heading', 'Verify Assignment')
@section('subheading', $assignment->asset->asset_code ?? '')

@push('styles')
<style>
    .detail-row{ display:flex; justify-content:space-between; padding:11px 0; border-bottom:1px dashed #EEF1F6; font-size:0.9rem; gap:16px; }
    .detail-row:last-child{ border-bottom:none; }
    .detail-row .label{ color:var(--muted); }
    .detail-row .value{ font-weight:600; text-align:right; }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">{{ $assignment->asset->name ?? 'Asset' }}</h3>
        <p class="text-muted mb-0">Asset ID: <span class="fw-semibold">{{ $assignment->asset->asset_code ?? '—' }}</span></p>
    </div>
    <a href="{{ route('my-assignments.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="content-card mb-4">
            <h5 class="mb-3">Assignment Details</h5>
            <div class="detail-row"><span class="label">Status</span><span class="value"><span class="badge bg-{{ $assignment->statusColor() }}">{{ $assignment->statusLabel() }}</span></span></div>
            <div class="detail-row"><span class="label">Assigned By</span><span class="value">{{ $assignment->assignedBy->name ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Assigned To</span><span class="value">{{ $assignment->custodian->name ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Department</span><span class="value">{{ $assignment->department }}</span></div>
            <div class="detail-row"><span class="label">Assignment Date</span><span class="value">{{ $assignment->assigned_date->format('d F Y') }}</span></div>
            @if($assignment->verified_at)
                <div class="detail-row"><span class="label">Verified On</span><span class="value">{{ $assignment->verified_at->format('d F Y, g:i A') }}</span></div>
            @endif
            @if($assignment->notes)
                <div class="detail-row"><span class="label">Notes</span><span class="value">{{ $assignment->notes }}</span></div>
            @endif
            @if($assignment->status === 'rejected' && $assignment->rejection_reason)
                <div class="detail-row"><span class="label">Rejection Reason</span><span class="value text-danger">{{ $assignment->rejection_reason }}</span></div>
            @endif
        </div>

        <div class="content-card">
            <h5 class="mb-3">Asset Information</h5>
            <div class="detail-row"><span class="label">Asset Code</span><span class="value">{{ $assignment->asset->asset_code ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Asset Name</span><span class="value">{{ $assignment->asset->name ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Category</span><span class="value">{{ $assignment->asset->category->name ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Description</span><span class="value">{{ $assignment->asset->description ?: '—' }}</span></div>
            <div class="detail-row"><span class="label">Current Status</span><span class="value">{{ $assignment->asset->assetStatus->name ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Location</span><span class="value">{{ $assignment->asset->location_detail ?: ($assignment->asset->location->name ?? '—') }}</span></div>
            <div class="detail-row"><span class="label">Serial / Supplier</span><span class="value">{{ $assignment->asset->supplier ?: '—' }}</span></div>
            <div class="detail-row"><span class="label">Purchase Date</span><span class="value">{{ optional($assignment->asset->purchase_date)->format('d F Y') ?? '—' }}</span></div>
        </div>
    </div>

    <div class="col-lg-5">
        @if($assignment->isPending())
            <div class="content-card">
                <h5 class="mb-2">Confirm Receipt</h5>
                <p class="text-muted small">Check the asset you physically received against the information shown. If everything matches, accept the assignment. Otherwise, reject it and let the asset officer know what is wrong.</p>

                <form method="POST" action="{{ route('my-assignments.accept', $assignment) }}" class="mb-3" onsubmit="return confirm('Accept this assignment? The asset will be officially assigned to you.');">
                    @csrf
                    <button type="submit" class="btn btn-success w-100"><i class="bi bi-check-circle me-1"></i> Accept Assignment</button>
                </form>

                <hr>

                <form method="POST" action="{{ route('my-assignments.reject', $assignment) }}" onsubmit="return confirm('Reject this assignment?');">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label fw-medium">Reason for rejection <span class="text-muted">(optional)</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="e.g. Serial number does not match, asset is damaged...">{{ old('rejection_reason') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-danger w-100"><i class="bi bi-x-circle me-1"></i> Reject Assignment</button>
                </form>
            </div>
        @else
            <div class="content-card text-center">
                <i class="bi bi-{{ $assignment->status === 'assigned' ? 'check-circle text-success' : ($assignment->status === 'rejected' ? 'x-circle text-danger' : 'dash-circle text-secondary') }} display-5"></i>
                <h5 class="mt-2 mb-1">{{ $assignment->statusLabel() }}</h5>
                <p class="text-muted small mb-0">
                    @if($assignment->status === 'assigned')
                        This asset is officially in your custody.
                    @elseif($assignment->status === 'rejected')
                        You rejected this assignment. The asset officer has been notified to reissue it.
                    @else
                        This assignment is no longer active.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

@endsection
