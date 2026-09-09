@extends('layouts.app')

@section('title', 'Issue Verification')
@section('heading', 'Issue Verification')
@section('subheading', 'Verify reported asset issues before recording maintenance')

@section('content')

<div class="mb-4">
    <h3 class="fw-bold mb-1">Issue Verification</h3>
    <p class="text-muted mb-0">Staff-reported asset faults. Test the asset, then accept the report to open maintenance, or reject it if the asset works properly.</p>
</div>

@if($pendingCount > 0)
    <div class="alert alert-warning d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div>You have <strong>{{ $pendingCount }}</strong> reported issue{{ $pendingCount > 1 ? 's' : '' }} waiting for verification.</div>
    </div>
@endif

<div class="card content-card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Report ID</th>
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
                        <td class="fw-semibold">{{ $report->report_code }}</td>
                        <td>
                            {{ $report->asset->asset_code ?? '—' }}
                            <span class="text-muted">{{ $report->asset->name ?? '' }}</span>
                        </td>
                        <td>{{ $report->reporter->name ?? '—' }}</td>
                        <td>{{ $report->created_at->format('d M Y, g:i A') }}</td>
                        <td><span class="badge bg-{{ $report->statusColor() }}">{{ $report->statusLabel() }}</span></td>
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
                    <tr><td colspan="6">
                        <div class="text-center py-5">
                            <i class="bi bi-shield-check display-4 text-muted"></i>
                            <h5 class="mt-3">No Reported Issues</h5>
                            <p class="text-muted">Nothing has been reported yet.</p>
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

@endsection
