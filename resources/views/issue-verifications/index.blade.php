@extends('layouts.app')

@section('title', 'Issue Verification')
@section('heading', 'Issue Verification')
@section('subheading', 'Verify reported asset issues before recording maintenance')

@section('content')

<x-banner title="Issue Verification" text="Staff-reported asset faults. Test the asset, then accept the report to open maintenance, or reject it if the asset works properly." />

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
        <div class="stat-icon icon-red"><i class="bi bi-tools"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Resolved</div><h2>{{ $resolvedCount }}</h2></div>
        <div class="stat-icon icon-green"><i class="bi bi-check-circle"></i></div>
    </div></div>
</div>

@if($pendingCount > 0)
    <div class="alert alert-warning d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div>You have <strong>{{ $pendingCount }}</strong> reported issue{{ $pendingCount > 1 ? 's' : '' }} waiting for verification.</div>
    </div>
@endif

<div class="card content-card p-4">
    @include('partials.list-filter', ['action' => route('issue-verifications.index'), 'placeholder' => 'Search report ID, asset, reporter...', 'options' => ['pending_verification' => 'Pending Verification', 'accepted' => 'Accepted — Under Maintenance', 'rejected' => 'Rejected', 'resolved' => 'Resolved']])

    <div class="table-responsive" data-live>
        <table class="table table-hover align-middle mb-0" data-sortable>
            <thead>
                <tr>
                    <th>Report ID</th>
                    <th>Picture</th>
                    <th>Asset</th>
                    <th>Reported By</th>
                    <th>Reported On</th>
                    <th>Status</th>
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
                        <td>{{ $report->reporter->name ?? '—' }}</td>
                        <td>{{ $report->created_at->format('d M Y, g:i A') }}</td>
                        <td><span class="badge bg-{{ $report->statusColor() }}">{{ $report->statusLabel() }}</span>@if($report->status === 'rejected' && $report->rejection_reason)<i class="bi bi-info-circle info-tip" data-bs-toggle="tooltip" title="{{ $report->rejection_reason }}"></i>@endif</td>
                        <td class="text-end">
                            @if($report->isPending())
                                <a href="{{ route('issue-verifications.show', $report) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-shield-check me-1"></i>Verify
                                </a>
                            @else
                                <a href="{{ route('issue-verifications.show', $report) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">
                        <div class="text-center py-5">
                            <i class="bi bi-shield-check display-4 text-muted"></i>
                            <h5 class="mt-3">No Reports Found</h5>
                            <p class="text-muted">Nothing has been reported yet.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2" data-live>
        <small class="text-muted">Showing {{ $reports->firstItem() ?? 0 }} to {{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }}</small>
        {{ $reports->links() }}
    </div>
</div>

@endsection
