@extends('layouts.app')

@section('title', 'Report Issues')
@section('heading', 'Report Issues')
@section('subheading', 'Report a damaged or faulty asset and track verification')

@section('content')

<x-banner title="Report Issues" text="If an asset assigned to you is damaged or not working, report it here. An asset officer will verify it before any maintenance is done." :keys="['N' => 'new', '/' => 'search']">
    @if($reportableAssets->isNotEmpty())
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reportIssueModal" data-key="n">
            <i class="bi bi-plus-circle me-2"></i>Report an Issue
        </button>
    @endif
</x-banner>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Total Reports</div><h2>{{ $totalCount }}</h2></div>
        <div class="stat-icon icon-blue"><i class="bi bi-clipboard-data"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Pending</div><h2>{{ $pendingCount }}</h2></div>
        <div class="stat-icon icon-orange"><i class="bi bi-hourglass-split"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Under Maintenance</div><h2>{{ $maintenanceCount }}</h2></div>
        <div class="stat-icon icon-green"><i class="bi bi-tools"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Rejected</div><h2>{{ $rejectedCount }}</h2></div>
        <div class="stat-icon icon-red"><i class="bi bi-x-circle"></i></div>
    </div></div>
</div>

@if($reportableAssets->isEmpty())
    <div class="alert alert-secondary d-flex align-items-center gap-2">
        <i class="bi bi-info-circle-fill"></i>
        <div>You have no assets assigned to you, so there is nothing to report yet.</div>
    </div>
@endif

<div class="card content-card p-4">
    @include('partials.list-filter', ['action' => route('issues.index'), 'placeholder' => 'Search report ID, asset, description...', 'options' => ['pending_verification' => 'Pending Verification', 'accepted' => 'Accepted — Under Maintenance', 'rejected' => 'Rejected', 'resolved' => 'Resolved']])

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" data-sortable>
            <thead>
                <tr>
                    <th>Report ID</th>
                    <th>Picture</th>
                    <th>Asset</th>
                    <th>Reported On</th>
                    <th>Status</th>
                    <th>Verified By</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                    <tr>
                        <td class="fw-semibold">{{ $report->report_code }}@if($report->photos_count)<span class="photo-count"><i class="bi bi-image"></i>{{ $report->photos_count }}</span>@endif</td>
                        <td>@include('partials.thumb', ['url' => $report->photoUrl(), 'alt' => 'Damage photo', 'name' => $report->report_code, 'info' => $report->asset->name ?? ''])</td>
                        <td>
                            {{ $report->asset->asset_code ?? '—' }}
                            <span class="text-muted">{{ $report->asset->name ?? '' }}</span>
                        </td>
                        <td>{{ $report->created_at->format('d M Y, g:i A') }}</td>
                        <td><span class="badge bg-{{ $report->statusColor() }}">{{ $report->statusLabel() }}</span>@if($report->status === 'rejected' && $report->rejection_reason)<i class="bi bi-info-circle info-tip" data-bs-toggle="tooltip" title="{{ $report->rejection_reason }}"></i>@endif</td>
                        <td>{{ $report->verifier->name ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('issues.show', $report) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye me-1"></i>View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">
                        <div class="empty-state">
                            <i class="bi bi-exclamation-triangle"></i>
                            <h6>No Reports Found</h6>
                            <span>Use "Report an Issue" if an asset is damaged or faulty.</span>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
        <small class="text-muted">Showing {{ $reports->firstItem() ?? 0 }} to {{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }}</small>
        {{ $reports->links() }}
    </div>
</div>

@if($reportableAssets->isNotEmpty())
<div class="modal fade" id="reportIssueModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Report an Issue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('issues.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Asset <span class="required">*</span></label>
                        <select name="asset_id" class="form-select" required>
                            <option value="" selected disabled>Select the affected asset</option>
                            @foreach($reportableAssets as $asset)
                                <option value="{{ $asset->id }}" @selected(old('asset_id') == $asset->id)>{{ $asset->asset_code }} - {{ $asset->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">What is wrong with it? <span class="required">*</span></label>
                        <textarea name="description" id="issueDescription" class="form-control" rows="4" maxlength="600" required
                            placeholder="Describe the damage or fault, e.g. the screen flickers and shuts down after a few minutes.">{{ old('description') }}</textarea>
                        <div class="form-text text-end" id="issueCount">0 / 600</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Photos of the damage <span class="required">*</span> <span class="text-muted fw-normal">(at least 1, up to {{ $maxPhotos }})</span></label>
                        @include('partials.photo-picker', ['name' => 'photos', 'max' => $maxPhotos, 'required' => true])
                        <div class="form-text"><i class="bi bi-camera me-1"></i>Click the box, drop pictures onto it, or take a photo. Photos are resized automatically.</div>
                    </div>
                    <p class="text-muted small mb-0">Your complaint will be sent to the asset officer for verification.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if($errors->any() && $reportableAssets->isNotEmpty())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('reportIssueModal')).show();
    });
</script>
@endif

@endsection

@push('scripts')
<script>
(function () {
    var box = document.getElementById('issueDescription'), out = document.getElementById('issueCount');
    if (!box) return;
    var show = function () { out.textContent = box.value.length + ' / 600'; };
    box.addEventListener('input', show); show();
})();
</script>
@endpush
