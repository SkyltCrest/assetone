@extends('layouts.app')

@section('title', 'Asset Maintenance')
@section('heading', 'Asset Maintenance')
@section('subheading', 'Record and manage maintenance activities for assets')

@php
    $statusBadges = ['pending' => 'warning', 'in_progress' => 'info', 'completed' => 'success', 'cancelled' => 'secondary'];
@endphp

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Asset Maintenance</h3>
        <p class="text-muted mb-0">Record maintenance work and keep track of what is due next.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMaintenanceModal">
        <i class="bi bi-plus-circle me-2"></i>Add Maintenance
    </button>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><a href="{{ route('maintenance.index') }}" class="stat-card d-flex justify-content-between align-items-start text-decoration-none">
        <div><div class="text-muted small text-uppercase">Total Maintenance</div><h2>{{ number_format($totalCount) }}</h2></div>
        <div class="stat-icon icon-blue"><i class="bi bi-tools"></i></div>
    </a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('maintenance.index', ['status' => 'pending']) }}" class="stat-card d-flex justify-content-between align-items-start text-decoration-none">
        <div><div class="text-muted small text-uppercase">Pending</div><h2>{{ number_format($pendingCount) }}</h2></div>
        <div class="stat-icon icon-orange"><i class="bi bi-clock"></i></div>
    </a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('maintenance.index', ['status' => 'in_progress']) }}" class="stat-card d-flex justify-content-between align-items-start text-decoration-none">
        <div><div class="text-muted small text-uppercase">In Progress</div><h2>{{ number_format($inProgressCount) }}</h2></div>
        <div class="stat-icon icon-green"><i class="bi bi-arrow-repeat"></i></div>
    </a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('maintenance.index', ['status' => 'overdue']) }}" class="stat-card d-flex justify-content-between align-items-start text-decoration-none">
        <div><div class="text-muted small text-uppercase">Overdue</div><h2>{{ number_format($overdueCount) }}</h2><div class="small text-muted mt-1">{{ $overdueCount ? 'need attention' : 'all on schedule' }}</div></div>
        <div class="stat-icon icon-red"><i class="bi bi-alarm"></i></div>
    </a></div>
</div>

<div class="content-card mb-4">
    <div class="mb-3"><h5 class="fw-bold mb-0">Upcoming Schedule</h5><small class="text-muted">Next maintenance dates, nearest first. Click a card for details.</small></div>
    <div class="up-strip">
        @forelse($upcoming as $item)
            @php
                [$dueLabel, $dueColor] = $item->dueStatus();
                $days = (int) today()->diffInDays($item->next_maintenance_date, false);
                $relative = match (true) {
                    $days < 0 => abs($days).' day'.(abs($days) === 1 ? '' : 's').' overdue',
                    $days === 0 => 'Due today',
                    $days === 1 => 'Tomorrow',
                    default => 'In '.$days.' days',
                };
            @endphp
            <button type="button" class="up-card up-{{ $dueColor }}" data-bs-toggle="modal" data-bs-target="#upcomingModal{{ $item->id }}">
                <div class="up-date"><b>{{ $item->next_maintenance_date->format('j') }}</b><small>{{ $item->next_maintenance_date->format('M') }}</small></div>
                <div class="up-info">
                    <b>{{ $item->asset->name ?? 'Asset' }}</b>
                    <small>{{ $types[$item->type] ?? $item->type }}</small>
                    <span class="rel">{{ $relative }}</span>
                </div>
            </button>
        @empty
            <div class="text-muted small p-2">No upcoming maintenance scheduled.</div>
        @endforelse
    </div>
</div>

<div class="card content-card p-4">
    <form method="GET" action="{{ route('maintenance.index') }}" class="row g-3 mb-4">
        <div class="col-12 col-md-5">
            <label class="form-label fw-semibold">Search</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search maintenance ID, asset or type...">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
                <option value="overdue" @selected($status === 'overdue')>Overdue</option>
            </select>
        </div>
        <div class="col-6 col-md-4">
            <label class="form-label fw-semibold">Maintenance Type</label>
            <select name="type" class="form-select" onchange="this.form.submit()">
                <option value="">All Maintenance Types</option>
                @foreach($types as $value => $label)
                    <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 text-nowrap">
            <thead>
                <tr>
                    <th>No.</th><th>Picture</th><th>Maintenance ID</th><th>Asset ID</th><th>Asset Name</th><th>Maintenance Type</th>
                    <th>Maintenance Date</th><th>Next Maintenance</th><th>Status</th><th>Due Status</th><th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($maintenances as $i => $record)
                    @php [$dueLabel, $dueColor] = $record->dueStatus(); @endphp
                    <tr>
                        <td>{{ $maintenances->firstItem() + $i }}</td>
                        <td>@include('partials.thumb', ['url' => $record->asset?->photoUrl(), 'alt' => $record->asset->name ?? 'Asset'])</td>
                        <td class="fw-semibold">{{ $record->maintenance_code }}</td>
                        <td>{{ $record->asset->asset_code ?? '—' }}</td>
                        <td class="text-dark">{{ $record->asset->name ?? '—' }}</td>
                        <td>{{ $types[$record->type] ?? $record->type }}</td>
                        <td>{{ $record->maintenance_date->format('d M Y') }}</td>
                        <td>{{ optional($record->next_maintenance_date)->format('d M Y') ?? '—' }}</td>
                        <td><span class="badge bg-{{ $statusBadges[$record->status] ?? 'secondary' }}">{{ $statuses[$record->status] ?? $record->status }}</span></td>
                        <td><span class="badge bg-{{ $dueColor }}">{{ $dueLabel }}</span></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary me-1" data-bs-toggle="modal" data-bs-target="#viewMaintenanceModal{{ $record->id }}" title="Details"><i class="bi bi-eye"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editMaintenanceModal{{ $record->id }}" title="Edit"><i class="bi bi-pencil"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteMaintenanceModal{{ $record->id }}" title="Delete"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11">
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-4 text-muted"></i>
                            <h5 class="mt-3">No Maintenance Records</h5>
                            <p class="text-muted">Try adjusting your search or filters.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
        <small class="text-muted">Showing {{ $maintenances->firstItem() ?? 0 }} to {{ $maintenances->lastItem() ?? 0 }} of {{ $maintenances->total() }} records</small>
        {{ $maintenances->links() }}
    </div>
</div>

@foreach($maintenances as $record)
    @include('maintenance._details', ['record' => $record, 'modalId' => 'viewMaintenanceModal'.$record->id])

    <div class="modal fade" id="editMaintenanceModal{{ $record->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Maintenance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('maintenance.update', $record) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        @include('maintenance._fields', ['record' => $record])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteMaintenanceModal{{ $record->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Delete Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Delete maintenance record <strong>{{ $record->maintenance_code }}</strong> for {{ $record->asset->name ?? 'this asset' }}? This cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('maintenance.destroy', $record) }}" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i>Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@foreach($upcoming as $item)
    @include('maintenance._details', ['record' => $item, 'modalId' => 'upcomingModal'.$item->id])
@endforeach

<div class="modal fade" id="addMaintenanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add Maintenance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('maintenance.store') }}">
                @csrf
                <div class="modal-body">
                    @include('maintenance._fields', ['record' => null])
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
