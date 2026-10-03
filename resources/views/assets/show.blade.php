@extends('layouts.app')

@section('title', $asset->asset_code)
@section('heading', 'Asset Details')
@section('subheading', $asset->asset_code)

@section('content')

@php
    $maintenanceTypes = \App\Http\Controllers\AssetMaintenanceController::TYPES;
    $maintenanceBadges = ['pending' => 'secondary', 'in_progress' => 'warning', 'completed' => 'success', 'cancelled' => 'dark'];
@endphp

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">{{ $asset->name }}</h3>
        <p class="text-muted mb-0">Asset Code: <span class="fw-semibold">{{ $asset->asset_code }}</span></p>
    </div>
    <div class="d-flex gap-2 flex-wrap no-print">
        <a href="{{ route('assets.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
        @if($asset->custodian_id === auth()->id())
            <a href="{{ route('issues.index') }}" class="btn btn-outline-warning"><i class="bi bi-exclamation-octagon me-1"></i> Report Issue</a>
        @endif
        @if(auth()->user()->canManageAssets())
            <a href="{{ route('assets.edit', $asset) }}" class="btn btn-outline-primary"><i class="bi bi-pencil-square me-1"></i> Update</a>
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAssetModal"><i class="bi bi-trash me-1"></i> Delete</button>
        @endif
        <button onclick="window.print()" class="btn btn-save"><i class="bi bi-printer me-1"></i> Print</button>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4 order-lg-2">
        <div class="content-card mb-4">
            <h5 class="mb-3">Picture of Asset</h5>
            @if($asset->photo)
                <a href="{{ $asset->photoUrl() }}" target="_blank" rel="noopener"><img src="{{ $asset->photoUrl() }}" alt="Photo of {{ $asset->name }}" class="asset-photo"></a>
            @else
                <div class="photo-empty asset-photo"><i class="bi bi-image"></i><span>No photo yet</span></div>
            @endif
        </div>

        <div class="content-card">
            <h5 class="mb-3">QR Code</h5>
            <div class="qr-box">
                <img src="{{ route('assets.qr', $asset) }}" alt="QR code for {{ $asset->asset_code }}" class="img-fluid mb-3" style="max-width:200px;">
                <div class="fw-semibold mb-2">{{ $asset->asset_code }}</div>
                <div><a href="{{ route('assets.qr', $asset) }}" download class="btn btn-outline-primary btn-sm no-print"><i class="bi bi-download me-1"></i> Download</a></div>
            </div>
        </div>
    </div>

    <div class="col-lg-8 order-lg-1">
        <div class="content-card mb-4">
            <h5 class="mb-3">Asset Information</h5>
            <div class="detail-row"><span class="label">Asset Name</span><span class="value">{{ $asset->name }}</span></div>
            <div class="detail-row"><span class="label">Category</span><span class="value">{{ $asset->category->name ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Asset Type</span><span class="value">{{ $asset->type->name ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Serial Number</span><span class="value">{{ $asset->serial_number ?: '—' }}</span></div>
            <div class="detail-row"><span class="label">Description</span><span class="value">{{ $asset->description ?: '—' }}</span></div>
            <div class="detail-row"><span class="label">Status</span><span class="value"><span class="badge bg-{{ $asset->assetStatus->badge_color ?? 'secondary' }}">{{ $asset->assetStatus->name ?? 'Unknown' }}</span></span></div>
        </div>

        <div class="content-card mb-4">
            <h5 class="mb-3">Purchase Information</h5>
            <div class="detail-row"><span class="label">Purchase Date</span><span class="value">{{ optional($asset->purchase_date)->format('d F Y') ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Purchase Price</span><span class="value">{{ $asset->purchase_price !== null ? 'RM '.number_format($asset->purchase_price, 2) : '—' }}</span></div>
            <div class="detail-row"><span class="label">Supplier</span><span class="value">{{ $asset->supplier ?: '—' }}</span></div>
            <div class="detail-row"><span class="label">Purchase Order / Reference No.</span><span class="value">{{ $asset->po_reference ?: '—' }}</span></div>
            <div class="detail-row"><span class="label">Warranty Expiry</span><span class="value">{{ optional($asset->warranty_expiry_date)->format('d F Y') ?? '—' }}</span></div>
        </div>

        <div class="content-card mb-4">
            <h5 class="mb-3">Asset Location</h5>
            <div class="detail-row"><span class="label">Department</span><span class="value">{{ $asset->department }}</span></div>
            <div class="detail-row"><span class="label">Location</span><span class="value">{{ $asset->location_detail }}</span></div>
            <div class="detail-row"><span class="label">Location Record</span><span class="value">{{ $asset->location ? $asset->location->code.' — '.$asset->location->name : '—' }}</span></div>
            @if($asset->location && $asset->location->place())
                <div class="detail-row"><span class="label">Building / Floor / Room</span><span class="value">{{ $asset->location->place() }}</span></div>
            @endif
        </div>

        <div class="content-card mb-4">
            <h5 class="mb-3">Asset Custodian</h5>
            <div class="detail-row"><span class="label">Person in Charge (PIC)</span><span class="value">{{ $asset->custodian->name ?? 'Unassigned' }}</span></div>
            <div class="detail-row"><span class="label">Assigned Date</span><span class="value">{{ optional($asset->assigned_date)->format('d F Y') ?? '—' }}</span></div>
        </div>

        <div class="content-card mb-4">
            <h5 class="mb-3">Assignment History</h5>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>PIC</th><th>Assigned</th><th>Due Date</th><th>Returned</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($asset->assignments->sortByDesc('assigned_date') as $assignment)
                            <tr>
                                <td>{{ $assignment->custodian->name ?? '—' }}</td>
                                <td>{{ optional($assignment->assigned_date)->format('d M Y') }}</td>
                                <td>{{ optional($assignment->due_date)->format('d M Y') ?? '—' }}</td>
                                <td>{{ optional($assignment->returned_date)->format('d M Y') ?? '—' }}</td>
                                <td><span class="badge bg-{{ $assignment->statusColor() }}">{{ $assignment->statusLabel() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No assignment history yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-card">
            <h5 class="mb-3">Maintenance History</h5>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Code</th><th>Type</th><th>Date</th><th>Next</th><th>Provider</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($asset->maintenances->sortByDesc('maintenance_date') as $record)
                            <tr>
                                <td class="fw-semibold">{{ $record->maintenance_code }}</td>
                                <td>{{ $maintenanceTypes[$record->type] ?? ucfirst($record->type) }}</td>
                                <td>{{ $record->maintenance_date->format('d M Y') }}</td>
                                <td>{{ optional($record->next_maintenance_date)->format('d M Y') ?? '—' }}</td>
                                <td>{{ $record->service_provider }}</td>
                                <td><span class="badge bg-{{ $maintenanceBadges[$record->status] ?? 'secondary' }}">{{ ucwords(str_replace('_', ' ', $record->status)) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No maintenance history yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->canManageAssets())
<div class="modal fade" id="deleteAssetModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Delete this asset?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2"><strong>{{ $asset->asset_code }} — {{ $asset->name }}</strong> will be removed permanently.</p>
                <p class="text-muted small mb-0">Its photo, assignment history, maintenance records and reported issues are deleted with it. This cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('assets.destroy', $asset) }}" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i> Delete Asset</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
