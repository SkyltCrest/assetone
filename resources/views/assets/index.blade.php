@extends('layouts.app')

@section('title', 'Asset Overview')
@section('heading', 'Asset Overview')
@section('subheading', 'Find and filter registered assets')

@section('content')

@php
    $canManage = auth()->user()->canManageAssets();
    $hasFilters = $search || $selectedCategory || $selectedLocation || $selectedStatus || $selectedDepartment || $purchaseFrom || $purchaseTo;
@endphp

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Asset Overview</h3>
        <p class="text-muted mb-0">Search across all registered assets by name, code, serial number, category, location, department or status.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#qrScanModal">
            <i class="bi bi-qr-code-scan me-2"></i>Scan QR
        </button>
        @if($canManage)
            <a href="{{ route('assets.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Register New Asset</a>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Total Assets</div><h2>{{ $totalCount }}</h2></div>
        <div class="stat-icon icon-blue"><i class="bi bi-box-seam"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Available</div><h2>{{ $availableCount }}</h2></div>
        <div class="stat-icon icon-green"><i class="bi bi-check-circle"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Assigned</div><h2>{{ $assignedCount }}</h2></div>
        <div class="stat-icon icon-orange"><i class="bi bi-person-check"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Maintenance</div><h2>{{ $maintenanceCount }}</h2></div>
        <div class="stat-icon icon-red"><i class="bi bi-tools"></i></div>
    </div></div>
</div>

<div class="card content-card p-4">
    <form method="GET" action="{{ route('assets.index') }}" class="row g-3 mb-4 align-items-end">
        <div class="col-md-6 col-xl-4">
            <label class="form-label" for="assetSearch">Search Asset</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" id="assetSearch" name="search" value="{{ $search }}" class="form-control" placeholder="Code, name or serial number...">
            </div>
        </div>
        <div class="col-md-6 col-xl-2">
            <label class="form-label" for="filterCategory">Category</label>
            <select id="filterCategory" name="category" class="form-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected($selectedCategory == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-xl-2">
            <label class="form-label" for="filterLocation">Location</label>
            <select id="filterLocation" name="location" class="form-select" onchange="this.form.submit()">
                <option value="">All Locations</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}" @selected($selectedLocation == $location->id)>{{ $location->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-xl-2">
            <label class="form-label" for="filterStatus">Status</label>
            <select id="filterStatus" name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}" @selected($selectedStatus == $status->id)>{{ $status->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-xl-2">
            <label class="form-label" for="filterDepartment">Department</label>
            <select id="filterDepartment" name="department" class="form-select" onchange="this.form.submit()">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department }}" @selected($selectedDepartment === $department)>{{ $department }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <label class="form-label" for="purchaseFrom">Purchase From</label>
            <input type="date" id="purchaseFrom" name="purchase_from" value="{{ $purchaseFrom }}" class="form-control" onchange="this.form.submit()">
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <label class="form-label" for="purchaseTo">Purchase To</label>
            <input type="date" id="purchaseTo" name="purchase_to" value="{{ $purchaseTo }}" class="form-control" onchange="this.form.submit()">
        </div>
        <div class="col-md-6 col-xl-8 d-flex gap-2 justify-content-md-end">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Search</button>
            @if($hasFilters)
                <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise me-1"></i>Reset</a>
            @endif
        </div>
    </form>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="fw-bold mb-0">Asset Records</h5>
        <div class="btn-group" role="group" aria-label="Choose view">
            <button type="button" class="btn btn-sm btn-outline-primary" data-view-btn="table" title="Table view"><i class="bi bi-list-ul"></i></button>
            <button type="button" class="btn btn-sm btn-outline-primary" data-view-btn="cards" title="Card view"><i class="bi bi-grid-3x3-gap"></i></button>
        </div>
    </div>

    @if($assets->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-search display-4 text-muted"></i>
            <h5 class="mt-3">No Asset Found</h5>
            <p class="text-muted">Try adjusting your search or filters.</p>
        </div>
    @else
        <div class="table-responsive" data-view="table">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Picture</th>
                        <th>Asset Code</th>
                        <th>Asset Name</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Custodian</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assets as $asset)
                        <tr>
                            <td>{{ $assets->firstItem() + $loop->index }}</td>
                            <td>@include('partials.thumb', ['url' => $asset->photoUrl(), 'alt' => $asset->name])</td>
                            <td class="fw-semibold"><a href="{{ route('assets.show', $asset) }}">{{ $asset->asset_code }}</a></td>
                            <td class="text-dark">{{ $asset->name }}<div class="small text-muted">{{ $asset->type->name ?? '' }}</div></td>
                            <td>{{ $asset->category->name ?? '—' }}</td>
                            <td>{{ $asset->location->name ?? $asset->location_detail ?? '—' }}</td>
                            <td>{{ $asset->custodian->name ?? 'Unassigned' }}</td>
                            <td><span class="badge bg-{{ $asset->assetStatus->badge_color ?? 'secondary' }}">{{ $asset->assetStatus->name ?? 'Unknown' }}</span></td>
                            <td class="text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" title="Quick view" data-bs-toggle="modal" data-bs-target="#assetDetail{{ $asset->id }}"><i class="bi bi-eye"></i></button>
                                @if($canManage)
                                    <a href="{{ route('assets.edit', $asset) }}" class="btn btn-sm btn-outline-secondary me-1" title="Update"><i class="bi bi-pencil-square"></i></a>
                                    <form method="POST" action="{{ route('assets.destroy', $asset) }}" class="d-inline"
                                          onsubmit="return confirm('Delete asset &quot;{{ $asset->name }}&quot;? This will also remove its photo, assignment, maintenance and issue history. This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row g-3 d-none" data-view="cards">
            @foreach($assets as $asset)
                <div class="col-sm-6 col-xl-4">
                    <div class="asset-tile" role="button" tabindex="0" data-bs-toggle="modal" data-bs-target="#assetDetail{{ $asset->id }}">
                        @if($asset->photo)
                            <img src="{{ $asset->photoUrl() }}" alt="{{ $asset->name }}" class="asset-tile-img" loading="lazy">
                        @else
                            <div class="asset-tile-img photo-empty"><i class="bi bi-image"></i></div>
                        @endif
                        <div class="asset-tile-body">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div class="fw-bold text-truncate">{{ $asset->name }}</div>
                                <span class="badge bg-{{ $asset->assetStatus->badge_color ?? 'secondary' }}">{{ $asset->assetStatus->name ?? 'Unknown' }}</span>
                            </div>
                            <div class="small text-muted">{{ $asset->asset_code }}</div>
                            <div class="small mt-2"><i class="bi bi-tags me-1"></i>{{ $asset->category->name ?? '—' }}</div>
                            <div class="small"><i class="bi bi-geo-alt me-1"></i>{{ $asset->location->name ?? $asset->location_detail ?? '—' }}</div>
                            <div class="small"><i class="bi bi-person me-1"></i>{{ $asset->custodian->name ?? 'Unassigned' }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
        <small class="text-muted">Showing {{ $assets->firstItem() ?? 0 }} to {{ $assets->lastItem() ?? 0 }} of {{ $assets->total() }} assets</small>
        {{ $assets->links() }}
    </div>
</div>

{{-- Quick-view pop-ups --}}
@foreach($assets as $asset)
<div class="modal fade" id="assetDetail{{ $asset->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Asset Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-md-5">
                        @if($asset->photo)
                            <img src="{{ $asset->photoUrl() }}" alt="{{ $asset->name }}" class="asset-photo" loading="lazy">
                        @else
                            <div class="photo-empty asset-photo"><i class="bi bi-image"></i><span>No photo yet</span></div>
                        @endif
                    </div>
                    <div class="col-md-7">
                        <h5 class="fw-bold mb-1">{{ $asset->name }}</h5>
                        <p class="text-muted mb-3">{{ $asset->asset_code }}</p>
                        <div class="detail-row"><span class="label">Category</span><span class="value">{{ $asset->category->name ?? '—' }}</span></div>
                        <div class="detail-row"><span class="label">Asset Type</span><span class="value">{{ $asset->type->name ?? '—' }}</span></div>
                        <div class="detail-row"><span class="label">Serial Number</span><span class="value">{{ $asset->serial_number ?: '—' }}</span></div>
                        <div class="detail-row"><span class="label">Status</span><span class="value"><span class="badge bg-{{ $asset->assetStatus->badge_color ?? 'secondary' }}">{{ $asset->assetStatus->name ?? 'Unknown' }}</span></span></div>
                        <div class="detail-row"><span class="label">Department</span><span class="value">{{ $asset->department ?: '—' }}</span></div>
                        <div class="detail-row"><span class="label">Location</span><span class="value">{{ $asset->location->name ?? $asset->location_detail ?? '—' }}</span></div>
                        <div class="detail-row"><span class="label">Custodian</span><span class="value">{{ $asset->custodian->name ?? 'Unassigned' }}</span></div>
                        <div class="detail-row"><span class="label">Purchase Date</span><span class="value">{{ optional($asset->purchase_date)->format('d M Y') ?? '—' }}</span></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                @if($canManage)
                    <a href="{{ route('assets.edit', $asset) }}" class="btn btn-outline-primary"><i class="bi bi-pencil-square me-1"></i>Update</a>
                @endif
                <a href="{{ route('assets.show', $asset) }}" class="btn btn-primary"><i class="bi bi-box-arrow-up-right me-1"></i>Full Details</a>
            </div>
        </div>
    </div>
</div>
@endforeach

{{-- Scan QR Modal --}}
<div class="modal fade" id="qrScanModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Scan Asset QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <video id="qrVideo" width="100%" class="rounded" autoplay muted playsinline></video>
                <canvas id="qrCanvas" style="display:none;"></canvas>
                <p id="qrText" class="mt-3 text-muted mb-2">Point the camera at a QR code</p>
                <input type="file" id="qrGalleryInput" accept="image/*" class="d-none">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="qrGalleryBtn">
                    <i class="bi bi-images me-1"></i>Scan From Gallery
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
<script>
// Table / card view switch, remembered per browser.
(function () {
    var views = document.querySelectorAll('[data-view]'), buttons = document.querySelectorAll('[data-view-btn]');
    if (!views.length) return;
    function show(name) {
        views.forEach(function (v) { v.classList.toggle('d-none', v.dataset.view !== name); });
        buttons.forEach(function (b) { b.classList.toggle('active', b.dataset.viewBtn === name); });
        try { localStorage.setItem('assetone_ov_view', name); } catch (e) {}
    }
    buttons.forEach(function (b) { b.addEventListener('click', function () { show(b.dataset.viewBtn); }); });
    var saved = 'table';
    try { saved = localStorage.getItem('assetone_ov_view') || 'table'; } catch (e) {}
    show(saved === 'cards' ? 'cards' : 'table');
})();

(function () {
    var qrModal = document.getElementById('qrScanModal');
    var qrVideo = document.getElementById('qrVideo');
    var qrCanvas = document.getElementById('qrCanvas');
    var qrText = document.getElementById('qrText');
    var scanning = false;
    var appOrigin = window.location.origin;

    function handleScannedCode(data) {
        if (data.indexOf(appOrigin) === 0) {
            qrText.innerHTML = "<span class='text-success fw-bold'>Asset found. Redirecting...</span>";
            scanning = false;
            window.location.href = data;
        } else {
            qrText.innerHTML = "<span class='text-warning'>Scanned code is not a recognised AssetOne asset.</span>";
        }
    }

    function tick() {
        if (!scanning) return;
        if (qrVideo.readyState === qrVideo.HAVE_ENOUGH_DATA) {
            qrCanvas.width = qrVideo.videoWidth;
            qrCanvas.height = qrVideo.videoHeight;
            var ctx = qrCanvas.getContext('2d');
            ctx.drawImage(qrVideo, 0, 0, qrCanvas.width, qrCanvas.height);
            var img = ctx.getImageData(0, 0, qrCanvas.width, qrCanvas.height);
            var code = jsQR(img.data, img.width, img.height);
            if (code) {
                handleScannedCode(code.data);
                return;
            }
        }
        requestAnimationFrame(tick);
    }

    qrModal.addEventListener('shown.bs.modal', function () {
        qrText.innerHTML = 'Point the camera at a QR code';
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } }).then(function (stream) {
            qrVideo.srcObject = stream;
            scanning = true;
            requestAnimationFrame(tick);
        }).catch(function () {
            qrText.innerHTML = "<span class='text-danger'>Camera access denied or unavailable. Use \"Scan From Gallery\" instead.</span>";
        });
    });

    qrModal.addEventListener('hidden.bs.modal', function () {
        scanning = false;
        if (qrVideo.srcObject) {
            qrVideo.srcObject.getTracks().forEach(function (t) { t.stop(); });
            qrVideo.srcObject = null;
        }
    });

    document.getElementById('qrGalleryBtn').addEventListener('click', function () {
        document.getElementById('qrGalleryInput').click();
    });

    document.getElementById('qrGalleryInput').addEventListener('change', function (e) {
        var file = e.target.files[0];
        if (!file) return;
        var img = new Image();
        img.onload = function () {
            qrCanvas.width = img.width;
            qrCanvas.height = img.height;
            var ctx = qrCanvas.getContext('2d');
            ctx.drawImage(img, 0, 0);
            var imgData = ctx.getImageData(0, 0, qrCanvas.width, qrCanvas.height);
            var code = jsQR(imgData.data, imgData.width, imgData.height);
            if (code) {
                handleScannedCode(code.data);
            } else {
                qrText.innerHTML = "<span class='text-warning'>No QR code found in the selected image.</span>";
            }
        };
        img.src = URL.createObjectURL(file);
    });
})();
</script>
@endpush
