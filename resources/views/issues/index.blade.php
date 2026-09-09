@extends('layouts.app')

@section('title', 'Report Issues')
@section('heading', 'Report Issues')
@section('subheading', 'Report a damaged or faulty asset and track verification')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Report Issues</h3>
        <p class="text-muted mb-0">If an asset assigned to you is damaged or not working, report it here. An asset officer will verify it before any maintenance is done.</p>
    </div>
    @if($reportableAssets->isNotEmpty())
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reportIssueModal">
            <i class="bi bi-plus-circle me-2"></i>Report an Issue
        </button>
    @endif
</div>

@if($reportableAssets->isEmpty())
    <div class="alert alert-secondary d-flex align-items-center gap-2">
        <i class="bi bi-info-circle-fill"></i>
        <div>You have no assets assigned to you, so there is nothing to report yet.</div>
    </div>
@endif

<div class="card content-card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Report ID</th>
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
                        <td class="fw-semibold">{{ $report->report_code }}</td>
                        <td>
                            {{ $report->asset->asset_code ?? '—' }}
                            <span class="text-muted">{{ $report->asset->name ?? '' }}</span>
                        </td>
                        <td>{{ $report->created_at->format('d M Y, g:i A') }}</td>
                        <td><span class="badge bg-{{ $report->statusColor() }}">{{ $report->statusLabel() }}</span></td>
                        <td>{{ $report->verifier->name ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('issues.show', $report) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye me-1"></i>View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">
                        <div class="text-center py-5">
                            <i class="bi bi-clipboard-check display-4 text-muted"></i>
                            <h5 class="mt-3">No Issues Reported</h5>
                            <p class="text-muted">You have not reported any issues yet.</p>
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
            <form method="POST" action="{{ route('issues.store') }}">
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
                    <div class="mb-2">
                        <label class="form-label">What is wrong with it? <span class="required">*</span></label>
                        <textarea name="description" class="form-control" rows="4" required
                            placeholder="Describe the damage or fault, e.g. the screen flickers and shuts down after a few minutes.">{{ old('description') }}</textarea>
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
