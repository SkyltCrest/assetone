@extends('layouts.app')

@section('title', 'Asset Maintenance')
@section('heading', 'Asset Maintenance')
@section('subheading', 'Record and manage maintenance activities for assets')

@php
    $statusBadges = ['pending' => 'warning', 'in_progress' => 'info', 'completed' => 'success'];
@endphp

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Asset Maintenance</h3>
        <p class="text-muted mb-0">Record and manage maintenance activities for assets.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMaintenanceModal">
        <i class="bi bi-plus-circle me-2"></i>Add Maintenance
    </button>
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted small text-uppercase fw-medium mb-1">Total</p>
                <h3 class="fw-bold mb-0">{{ number_format($totalCount) }}</h3>
            </div>
            <div class="stat-icon icon-blue"><i class="bi bi-tools"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted small text-uppercase fw-medium mb-1">Pending</p>
                <h3 class="fw-bold mb-0">{{ number_format($pendingCount) }}</h3>
            </div>
            <div class="stat-icon icon-orange"><i class="bi bi-clock"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted small text-uppercase fw-medium mb-1">In Progress</p>
                <h3 class="fw-bold mb-0">{{ number_format($inProgressCount) }}</h3>
            </div>
            <div class="stat-icon icon-blue"><i class="bi bi-arrow-repeat"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted small text-uppercase fw-medium mb-1">Completed</p>
                <h3 class="fw-bold mb-0">{{ number_format($completedCount) }}</h3>
            </div>
            <div class="stat-icon icon-green"><i class="bi bi-check-circle"></i></div>
        </div>
    </div>
</div>

<div class="card content-card p-4">
    <form method="GET" action="{{ route('maintenance.index') }}" class="row g-3 mb-4">
        <div class="col-12 col-md-5">
            <label class="form-label fw-semibold">Search</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search maintenance ID, asset...">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-4">
            <label class="form-label fw-semibold">Maintenance Type</label>
            <select name="type" class="form-select" onchange="this.form.submit()">
                <option value="">All Types</option>
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
                    <th>No.</th><th>Maintenance ID</th><th>Asset</th><th>Type</th><th>Date</th>
                    <th>Provider</th><th>Cost</th><th>Status</th><th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($maintenances as $i => $record)
                    <tr>
                        <td>{{ $maintenances->firstItem() + $i }}</td>
                        <td class="fw-semibold">{{ $record->maintenance_code }}</td>
                        <td>{{ $record->asset->asset_code ?? '—' }} <span class="text-muted">{{ $record->asset->name ?? '' }}</span></td>
                        <td>{{ $types[$record->type] ?? $record->type }}</td>
                        <td>{{ $record->maintenance_date->format('d M Y') }}</td>
                        <td>{{ $record->service_provider }}</td>
                        <td>{{ $record->cost !== null ? 'RM '.number_format($record->cost, 2) : '—' }}</td>
                        <td><span class="badge bg-{{ $statusBadges[$record->status] ?? 'secondary' }}">{{ $statuses[$record->status] ?? $record->status }}</span></td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editMaintenanceModal{{ $record->id }}" title="Edit"><i class="bi bi-pencil"></i></button>
                            <form method="POST" action="{{ route('maintenance.destroy', $record) }}" class="d-inline" onsubmit="return confirm('Delete this maintenance record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>

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
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Asset <span class="required">*</span></label>
                                                <select name="asset_id" class="form-select" required>
                                                    @foreach($assets as $asset)
                                                        <option value="{{ $asset->id }}" @selected($record->asset_id === $asset->id)>{{ $asset->asset_code }} - {{ $asset->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Maintenance Type <span class="required">*</span></label>
                                                <select name="type" class="form-select" required>
                                                    @foreach($types as $value => $label)
                                                        <option value="{{ $value }}" @selected($record->type === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Maintenance Date <span class="required">*</span></label>
                                                <input type="date" name="maintenance_date" value="{{ $record->maintenance_date->format('Y-m-d') }}" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Service Provider <span class="required">*</span></label>
                                                <input type="text" name="service_provider" value="{{ $record->service_provider }}" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Cost (RM)</label>
                                                <input type="number" step="0.01" min="0" name="cost" value="{{ $record->cost }}" class="form-control">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Status <span class="required">*</span></label>
                                                <select name="status" class="form-select" required>
                                                    @foreach($statuses as $value => $label)
                                                        <option value="{{ $value }}" @selected($record->status === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Notes</label>
                                                <textarea name="description" class="form-control" rows="3">{{ $record->description }}</textarea>
                                            </div>
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
                    <tr><td colspan="9">
                        <div class="text-center py-5">
                            <i class="bi bi-tools display-4 text-muted"></i>
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
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Asset <span class="required">*</span></label>
                            <select name="asset_id" class="form-select" required>
                                <option value="" selected disabled>Select asset</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}">{{ $asset->asset_code }} - {{ $asset->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Maintenance Type <span class="required">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="" selected disabled>Select type</option>
                                @foreach($types as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Maintenance Date <span class="required">*</span></label>
                            <input type="date" name="maintenance_date" value="{{ now()->format('Y-m-d') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Service Provider <span class="required">*</span></label>
                            <input type="text" name="service_provider" class="form-control" placeholder="e.g. In-house IT Team" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cost (RM)</label>
                            <input type="number" step="0.01" min="0" name="cost" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <select name="status" class="form-select" required>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected($value === 'pending')>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter maintenance details..."></textarea>
                        </div>
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
