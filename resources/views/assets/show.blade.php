@extends('layouts.app')

@section('title', $asset->asset_code)
@section('heading', 'Asset Details')
@section('subheading', $asset->asset_code)

@push('styles')
<style>
    .detail-row{ display:flex; justify-content:space-between; padding:11px 0; border-bottom:1px dashed #EEF1F6; font-size:0.9rem; }
    .detail-row:last-child{ border-bottom:none; }
    .detail-row .label{ color:var(--muted); }
    .detail-row .value{ font-weight:600; text-align:right; }
    .qr-box{ border:1.5px dashed #E4E8F0; border-radius:14px; padding:20px; text-align:center; }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">{{ $asset->name }}</h3>
        <p class="text-muted mb-0">Asset ID: <span class="fw-semibold">{{ $asset->asset_code }}</span></p>
    </div>
    <div class="d-flex gap-2 no-print">
        <a href="{{ route('home') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
        @if($asset->custodian_id === auth()->id())
            <a href="{{ route('issues.index') }}" class="btn btn-outline-warning"><i class="bi bi-exclamation-octagon me-1"></i> Report Issue</a>
        @endif
        @if(auth()->user()->canManageAssets())
            <a href="{{ route('assets.edit', $asset) }}" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i> Edit</a>
        @endif
        <button onclick="window.print()" class="btn btn-save"><i class="bi bi-printer me-1"></i> Print</button>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="content-card mb-4">
            <h5 class="mb-3">Basic Information</h5>
            <div class="detail-row"><span class="label">Asset Name</span><span class="value">{{ $asset->name }}</span></div>
            <div class="detail-row"><span class="label">Category</span><span class="value">{{ $asset->category->name ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Description</span><span class="value">{{ $asset->description ?: '—' }}</span></div>
            <div class="detail-row"><span class="label">Status</span><span class="value"><span class="badge bg-{{ $asset->assetStatus->badge_color ?? 'secondary' }}">{{ $asset->assetStatus->name ?? 'Unknown' }}</span></span></div>
        </div>

        <div class="content-card mb-4">
            <h5 class="mb-3">Purchase Information</h5>
            <div class="detail-row"><span class="label">Purchase Date</span><span class="value">{{ optional($asset->purchase_date)->format('d F Y') ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Purchase Price</span><span class="value">{{ $asset->purchase_price !== null ? 'RM '.number_format($asset->purchase_price, 2) : '—' }}</span></div>
            <div class="detail-row"><span class="label">Supplier</span><span class="value">{{ $asset->supplier ?: '—' }}</span></div>
            <div class="detail-row"><span class="label">Warranty Expiry</span><span class="value">{{ optional($asset->warranty_expiry_date)->format('d F Y') ?? '—' }}</span></div>
        </div>

        <div class="content-card mb-4">
            <h5 class="mb-3">Location &amp; Assignment</h5>
            <div class="detail-row"><span class="label">Department</span><span class="value">{{ $asset->department }}</span></div>
            <div class="detail-row"><span class="label">Location</span><span class="value">{{ $asset->location_detail }}</span></div>
            <div class="detail-row"><span class="label">Linked Location Record</span><span class="value">{{ $asset->location->name ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Custodian</span><span class="value">{{ $asset->custodian->name ?? 'Unassigned' }}</span></div>
        </div>

        <div class="content-card mb-4">
            <h5 class="mb-3">Assignment History</h5>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Custodian</th><th>Assigned Date</th><th>Returned Date</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($asset->assignments as $assignment)
                            <tr>
                                <td>{{ $assignment->custodian->name ?? '—' }}</td>
                                <td>{{ optional($assignment->assigned_date)->format('d F Y') }}</td>
                                <td>{{ optional($assignment->returned_date)->format('d F Y') ?? '—' }}</td>
                                <td><span class="badge bg-{{ $assignment->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($assignment->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No assignment history yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="content-card">
            <h5 class="mb-3">Maintenance History</h5>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Code</th><th>Type</th><th>Date</th><th>Provider</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($asset->maintenances as $record)
                            <tr>
                                <td class="fw-semibold">{{ $record->maintenance_code }}</td>
                                <td>{{ ucfirst($record->type) }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($record->maintenance_date)->format('d F Y') }}</td>
                                <td>{{ $record->service_provider }}</td>
                                <td><span class="badge bg-{{ $record->status === 'completed' ? 'success' : ($record->status === 'in_progress' ? 'warning' : 'secondary') }}">{{ ucwords(str_replace('_',' ',$record->status)) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No maintenance history yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="content-card">
            <h5 class="mb-3">QR Code</h5>
            <div class="qr-box">
                <img src="{{ route('assets.qr', $asset) }}" alt="QR code for {{ $asset->asset_code }}" class="img-fluid mb-3" style="max-width:200px;">
                <div><a href="{{ route('assets.qr', $asset) }}" download class="btn btn-outline-primary btn-sm no-print"><i class="bi bi-download me-1"></i> Download</a></div>
            </div>
        </div>
    </div>
</div>

@endsection
