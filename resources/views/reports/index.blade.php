@extends('layouts.app')

@section('title', 'Asset Report')
@section('heading', 'Asset Report')
@section('subheading', 'View a summary of assets managed by MDPT')

@section('content')

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Asset Report</h3>
        <p class="text-muted mb-0">View a summary of assets managed by MDPT.</p>
    </div>
    <button class="btn btn-primary no-print" onclick="window.print()">
        <i class="bi bi-printer me-2"></i>Print Report
    </button>
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-md-4">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted small text-uppercase fw-medium mb-1">Total Assets</p>
                <h3 class="fw-bold mb-0">{{ number_format($totalAssets) }}</h3>
            </div>
            <div class="stat-icon icon-blue"><i class="bi bi-box-seam"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted small text-uppercase fw-medium mb-1">Assigned</p>
                <h3 class="fw-bold mb-0">{{ number_format($assignedAssets) }}</h3>
            </div>
            <div class="stat-icon icon-green"><i class="bi bi-person-check"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted small text-uppercase fw-medium mb-1">Unassigned</p>
                <h3 class="fw-bold mb-0">{{ number_format($unassignedAssets) }}</h3>
            </div>
            <div class="stat-icon icon-orange"><i class="bi bi-person-dash"></i></div>
        </div>
    </div>
</div>

<div class="card content-card p-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
        <h5 class="fw-bold mb-0">Asset Summary</h5>
        <form method="GET" action="{{ route('reports.index') }}" class="no-print">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                @foreach($statuses as $assetStatus)
                    <option value="{{ $assetStatus->id }}" @selected($selectedStatus == $assetStatus->id)>{{ $assetStatus->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>No.</th><th>Asset ID</th><th>Asset Name</th><th>Category</th><th>Location</th><th>Custodian</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $i => $asset)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $asset->asset_code }}</td>
                        <td>{{ $asset->name }}</td>
                        <td>{{ $asset->category->name ?? '—' }}</td>
                        <td>{{ $asset->location->name ?? $asset->location_detail ?? '—' }}</td>
                        <td>{{ $asset->custodian->name ?? 'Unassigned' }}</td>
                        <td><span class="badge bg-{{ $asset->assetStatus->badge_color ?? 'secondary' }}">{{ $asset->assetStatus->name ?? 'Unknown' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No assets found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
