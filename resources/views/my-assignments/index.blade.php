@extends('layouts.app')

@section('title', 'My Assignments')
@section('heading', 'My Assignments')
@section('subheading', 'Assets assigned to you and their verification status')

@section('content')

<div class="mb-4">
    <h3 class="fw-bold mb-1">My Assignments</h3>
    <p class="text-muted mb-0">Review assets assigned to you. Accept an assignment to confirm the asset matches the record, or reject it if something is wrong.</p>
</div>

@if($pendingCount > 0)
    <div class="alert alert-warning d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div>You have <strong>{{ $pendingCount }}</strong> assignment{{ $pendingCount > 1 ? 's' : '' }} waiting for your verification.</div>
    </div>
@endif

<div class="card content-card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Asset</th>
                    <th>Assigned By</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $i => $assignment)
                    <tr>
                        <td>{{ $assignments->firstItem() + $i }}</td>
                        <td class="fw-semibold">
                            {{ $assignment->asset->asset_code ?? '—' }}
                            <span class="text-muted fw-normal">{{ $assignment->asset->name ?? '' }}</span>
                        </td>
                        <td>{{ $assignment->assignedBy->name ?? '—' }}</td>
                        <td>{{ $assignment->assigned_date->format('d M Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $assignment->statusColor() }}">{{ $assignment->statusLabel() }}</span>
                            @if($assignment->status === 'rejected' && $assignment->rejection_reason)
                                <i class="bi bi-info-circle text-muted ms-1" title="{{ $assignment->rejection_reason }}"></i>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($assignment->isPending())
                                <a href="{{ route('my-assignments.show', $assignment) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-shield-check me-1"></i>Verify
                                </a>
                            @else
                                <a href="{{ route('my-assignments.show', $assignment) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <h5 class="mt-3">No Assignments</h5>
                            <p class="text-muted">You have no assets assigned to you yet.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
        <small class="text-muted">Showing {{ $assignments->firstItem() ?? 0 }} to {{ $assignments->lastItem() ?? 0 }} of {{ $assignments->total() }}</small>
        {{ $assignments->links() }}
    </div>
</div>

@endsection
