@extends('layouts.app')

@section('title', 'My Assignments')
@section('heading', 'My Assignments')
@section('subheading', 'Assets assigned to you and their verification status')

@section('content')

<x-banner title="My Assignments" text="Review assets assigned to you. Accept an assignment to confirm the asset matches the record, or reject it if something is wrong." />

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Total</div><h2>{{ $totalCount }}</h2></div>
        <div class="stat-icon icon-blue"><i class="bi bi-clipboard-check"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Pending</div><h2>{{ $pendingCount }}</h2></div>
        <div class="stat-icon icon-orange"><i class="bi bi-hourglass-split"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Accepted</div><h2>{{ $acceptedCount }}</h2></div>
        <div class="stat-icon icon-green"><i class="bi bi-check-circle"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Rejected</div><h2>{{ $rejectedCount }}</h2></div>
        <div class="stat-icon icon-red"><i class="bi bi-x-circle"></i></div>
    </div></div>
</div>

@if($pendingCount > 0)
    <div class="alert alert-warning d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div>You have <strong>{{ $pendingCount }}</strong> assignment{{ $pendingCount > 1 ? 's' : '' }} waiting for your verification.</div>
    </div>
@endif

<div class="card content-card p-4">
    @include('partials.list-filter', ['action' => route('my-assignments.index'), 'placeholder' => 'Search asset code, name, assigned by...', 'options' => ['pending_verification' => 'Pending', 'assigned' => 'Accepted', 'rejected' => 'Rejected', 'unassigned' => 'Returned']])

    <div class="table-responsive" data-live>
        <table class="table table-hover align-middle mb-0" data-sortable>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Photo</th>
                    <th>Asset</th>
                    <th>Assigned By</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $i => $assignment)
                    <tr>
                        <td>{{ $assignments->firstItem() + $i }}</td>
                        <td>@include('partials.thumb', ['url' => $assignment->asset?->photoUrl(), 'alt' => $assignment->asset->name ?? 'Asset'])</td>
                        <td class="fw-semibold">
                            {{ $assignment->asset->asset_code ?? '—' }}
                            <span class="text-muted fw-normal">{{ $assignment->asset->name ?? '' }}</span>
                        </td>
                        <td>{{ $assignment->assignedBy->name ?? '—' }}</td>
                        <td>{{ $assignment->assigned_date->format('d M Y') }}</td>
                        <td class="{{ $assignment->isOverdue() ? 'text-danger fw-semibold' : '' }}">{{ optional($assignment->due_date)->format('d M Y') ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $assignment->statusColor() }}">{{ $assignment->statusLabel() }}</span>
                            @if($assignment->status === 'rejected' && $assignment->rejection_reason)
                                <i class="bi bi-info-circle info-tip" data-bs-toggle="tooltip" title="{{ $assignment->rejection_reason }}"></i>
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
                    <tr><td colspan="8">
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <h5 class="mt-3">No Assignment Found</h5>
                            <p class="text-muted">You have no assets assigned to you yet.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2" data-live>
        <small class="text-muted">Showing {{ $assignments->firstItem() ?? 0 }} to {{ $assignments->lastItem() ?? 0 }} of {{ $assignments->total() }}</small>
        {{ $assignments->links() }}
    </div>
</div>

@endsection
