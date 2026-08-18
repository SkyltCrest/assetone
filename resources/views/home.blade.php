@extends('layouts.app')

@section('title', 'Home')
@section('heading', 'Welcome back')
@section('subheading', 'AssetOne — Sistem Pengurusan Aset Pejabat Daerah Perak Tengah')

@section('content')

@php
    $user = auth()->user();
    $maintenanceBadges = ['pending' => 'secondary', 'in_progress' => 'warning', 'completed' => 'success'];
@endphp

<div class="content-card mb-4">
    <h3 class="fw-bold mb-2">Hello, {{ $user->name }} 👋</h3>
    <p class="text-muted mb-0">
        You're signed in as <strong>{{ ucwords(str_replace('_', ' ', $user->role)) }}</strong>.
    </p>
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon icon-blue mb-3"><i class="bi bi-box-seam"></i></div>
            <p class="text-muted mb-1">Registered Assets</p>
            <h3>{{ $assetTotal }}</h3>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon icon-green mb-3"><i class="bi bi-tags"></i></div>
            <p class="text-muted mb-1">Asset Categories</p>
            <h3>{{ $categoryTotal }}</h3>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon icon-orange mb-3"><i class="bi bi-geo-alt"></i></div>
            <p class="text-muted mb-1">Asset Locations</p>
            <h3>{{ $locationTotal }}</h3>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon icon-red mb-3"><i class="bi bi-people"></i></div>
            <p class="text-muted mb-1">System Users</p>
            <h3>{{ $userTotal }}</h3>
        </div>
    </div>
</div>

@if($statusBreakdown->isNotEmpty())
<div class="content-card mb-4">
    <h5 class="fw-bold mb-4">Asset Status Overview</h5>
    @foreach($statusBreakdown as $status)
        @php $percent = $assetTotal > 0 ? round($status->assets_count / $assetTotal * 100) : 0; @endphp
        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <span>{{ $status->name }}</span>
                <span class="fw-semibold">{{ $status->assets_count }} ({{ $percent }}%)</span>
            </div>
            <div class="progress mt-2" style="height:8px;">
                <div class="progress-bar bg-{{ $status->badge_color }}" style="width:{{ $percent }}%;"></div>
            </div>
        </div>
    @endforeach
</div>
@endif

<div class="content-card">
    <h5 class="mb-3">Available Modules</h5>
    <div class="row g-3">
        @if($user->canManageAssets())
        <div class="col-md-4">
            <a href="{{ route('assets.create') }}" class="text-decoration-none">
                <div class="border rounded-3 p-3 h-100" style="border-color:#E7EAF1 !important;">
                    <i class="bi bi-box-seam fs-4 text-primary"></i>
                    <div class="fw-semibold mt-2">Asset Registration</div>
                    <div class="text-muted small">Register a new asset</div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('asset-management.index') }}" class="text-decoration-none">
                <div class="border rounded-3 p-3 h-100" style="border-color:#E7EAF1 !important;">
                    <i class="bi bi-tags fs-4 text-primary"></i>
                    <div class="fw-semibold mt-2">Asset Management</div>
                    <div class="text-muted small">Manage categories, locations & statuses</div>
                </div>
            </a>
        </div>
        @endif
        @if($user->isAdministrator())
        <div class="col-md-4">
            <a href="{{ route('users.index') }}" class="text-decoration-none">
                <div class="border rounded-3 p-3 h-100" style="border-color:#E7EAF1 !important;">
                    <i class="bi bi-people fs-4 text-primary"></i>
                    <div class="fw-semibold mt-2">User Management</div>
                    <div class="text-muted small">Manage system users</div>
                </div>
            </a>
        </div>
        @endif
    </div>
</div>

<div class="content-card mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Recent Assets</h5>
        <a href="{{ route('assets.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Asset ID</th><th>Asset Name</th><th>Category</th><th>Location</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($recentAssets as $asset)
                    <tr>
                        <td class="fw-semibold"><a href="{{ route('assets.show', $asset) }}">{{ $asset->asset_code }}</a></td>
                        <td>{{ $asset->name }}</td>
                        <td>{{ $asset->category->name ?? '—' }}</td>
                        <td>{{ $asset->location->name ?? $asset->location_detail ?? '—' }}</td>
                        <td><span class="badge bg-{{ $asset->assetStatus->badge_color ?? 'secondary' }}">{{ $asset->assetStatus->name ?? 'Unknown' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No assets registered yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($recentMaintenance->isNotEmpty())
<div class="content-card mt-4">
    <h5 class="fw-bold mb-3">Recent Maintenance</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Asset ID</th><th>Asset Name</th><th>Issue</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($recentMaintenance as $record)
                    <tr>
                        <td class="fw-semibold">{{ $record->asset->asset_code ?? '—' }}</td>
                        <td>{{ $record->asset->name ?? '—' }}</td>
                        <td>{{ $record->description ?: ucfirst($record->type) }}</td>
                        <td>{{ $record->maintenance_date->format('d M Y') }}</td>
                        <td><span class="badge bg-{{ $maintenanceBadges[$record->status] ?? 'secondary' }}">{{ ucwords(str_replace('_', ' ', $record->status)) }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
