@extends('layouts.app')

@section('title', 'Verify Issue')
@section('heading', 'Verify Reported Issue')
@section('subheading', $report->report_code)

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
        <h3 class="fw-bold mb-1">{{ $report->asset->name ?? 'Asset' }}</h3>
        <p class="text-muted mb-0">Report {{ $report->report_code }} &middot; Asset ID: <span class="fw-semibold">{{ $report->asset->asset_code ?? '—' }}</span></p>
    </div>
    <a href="{{ route('issue-verifications.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="content-card mb-4">
            <h5 class="mb-3">Complaint</h5>
            <div class="detail-row"><span class="label">Status</span><span class="value"><span class="badge bg-{{ $report->statusColor() }}">{{ $report->statusLabel() }}</span></span></div>
            <div class="detail-row"><span class="label">Reported By</span><span class="value">{{ $report->reporter->name ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Reported On</span><span class="value">{{ $report->created_at->format('d F Y, g:i A') }}</span></div>
            <div class="detail-row"><span class="label">Reported Fault</span><span class="value" style="max-width:60%;">{{ $report->description }}</span></div>
            @if($report->verified_at)
                <div class="detail-row"><span class="label">Verified By</span><span class="value">{{ $report->verifier->name ?? '—' }} on {{ $report->verified_at->format('d F Y, g:i A') }}</span></div>
            @endif
            @if($report->status === \App\Models\IssueReport::STATUS_REJECTED && $report->rejection_reason)
                <div class="detail-row"><span class="label">Reason for Rejection</span><span class="value text-danger" style="max-width:60%;">{{ $report->rejection_reason }}</span></div>
            @endif
        </div>

        <div class="content-card">
            <h5 class="mb-3">Asset Information</h5>
            <div class="detail-row"><span class="label">Asset Code</span><span class="value">{{ $report->asset->asset_code ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Asset Name</span><span class="value">{{ $report->asset->name ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Category</span><span class="value">{{ $report->asset->category->name ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Current Status</span><span class="value">{{ $report->asset->assetStatus->name ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Location</span><span class="value">{{ $report->asset->location_detail ?: ($report->asset->location->name ?? '—') }}</span></div>
            <div class="detail-row"><span class="label">Custodian</span><span class="value">{{ $report->asset->custodian->name ?? 'Unassigned' }}</span></div>
        </div>

        @if($report->maintenance)
            <div class="content-card mt-4">
                <h5 class="mb-3">Maintenance Opened</h5>
                <div class="detail-row"><span class="label">Maintenance ID</span><span class="value">{{ $report->maintenance->maintenance_code }}</span></div>
                <div class="detail-row"><span class="label">Type</span><span class="value">{{ ucfirst($report->maintenance->type) }}</span></div>
                <div class="detail-row"><span class="label">Service Provider</span><span class="value">{{ $report->maintenance->service_provider }}</span></div>
                <div class="detail-row"><span class="label">Maintenance Status</span><span class="value">{{ ucwords(str_replace('_', ' ', $report->maintenance->status)) }}</span></div>
                <p class="text-muted small mb-0 mt-2">Update this record in the <a href="{{ route('maintenance.index') }}">Maintenance</a> module. When it is marked completed, the asset returns to In Use and this report is resolved automatically.</p>
            </div>
        @endif
    </div>

    <div class="col-lg-5">
        @if($report->isPending())
            <div class="content-card mb-4">
                <h5 class="mb-2 text-success">Accept — asset is damaged</h5>
                <p class="text-muted small">You tested the asset and confirmed it is not working properly. Accepting opens a maintenance record and moves the asset to <strong>Under Maintenance</strong>.</p>
                <form method="POST" action="{{ route('issue-verifications.accept', $report) }}"
                      onsubmit="return confirm('Accept this report? The asset will be moved to Under Maintenance.');">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label fw-medium">Maintenance type <span class="required">*</span></label>
                        <select name="type" class="form-select" required>
                            @foreach($maintenanceTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('type', 'repair') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-medium">Service provider / assigned to <span class="required">*</span></label>
                        <input type="text" name="service_provider" class="form-control" required
                               value="{{ old('service_provider') }}" placeholder="e.g. In-house IT Team">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Note <span class="text-muted">(optional)</span></label>
                        <textarea name="note" class="form-control" rows="2" placeholder="What you found when testing the asset...">{{ old('note') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i class="bi bi-check-circle me-1"></i> Accept &amp; Start Maintenance</button>
                </form>
            </div>

            <div class="content-card">
                <h5 class="mb-2 text-danger">Reject — asset works fine</h5>
                <p class="text-muted small">You tested the asset and it is functioning properly. The complaint will be closed and the staff member notified.</p>
                <form method="POST" action="{{ route('issue-verifications.reject', $report) }}"
                      onsubmit="return confirm('Reject this report?');">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label fw-medium">Reason for rejection <span class="required">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required
                                  placeholder="e.g. Tested for 30 minutes, no fault found. Battery was simply flat.">{{ old('rejection_reason') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-danger w-100"><i class="bi bi-x-circle me-1"></i> Reject Report</button>
                </form>
            </div>
        @else
            <div class="content-card text-center">
                <i class="bi bi-{{ $report->status === \App\Models\IssueReport::STATUS_REJECTED ? 'x-circle text-danger' : 'check-circle text-success' }} display-5"></i>
                <h5 class="mt-2 mb-1">{{ $report->statusLabel() }}</h5>
                <p class="text-muted small mb-0">
                    @if($report->status === \App\Models\IssueReport::STATUS_REJECTED)
                        This report was rejected. No maintenance was recorded.
                    @elseif($report->status === \App\Models\IssueReport::STATUS_RESOLVED)
                        Maintenance is complete and the asset is back In Use.
                    @else
                        The asset is Under Maintenance. It returns to In Use when the maintenance record is completed.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

@endsection
