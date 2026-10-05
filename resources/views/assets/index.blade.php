@extends('layouts.app')

@section('title', 'Asset Overview')
@section('heading', 'Asset Overview')
@section('subheading', 'Find and filter registered assets')

@section('content')

@php
    $canManage = auth()->user()->canManageAssets();
    $hasFilters = $search || $selectedCategory || $selectedLocation || $selectedStatus || $selectedDepartment || $purchaseFrom || $purchaseTo;
@endphp

<x-banner title="Asset Overview" text="Find asset records using multiple search and filter options." :keys="['/' => 'search', 'S' => 'scan']">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#qrScanModal" data-key="s">
        <i class="bi bi-qr-code-scan me-1"></i> Scan QR
    </button>
    @if($canManage)
        <a href="{{ route('assets.create') }}" class="btn btn-light" data-key="n"><i class="bi bi-plus-circle me-2"></i>Register New Asset</a>
    @endif
</x-banner>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Total Assets</div><h2>{{ $totalCount }}</h2><div class="stat-sub" data-live>{{ $assets->total() }} matching your filters</div></div>
        <div class="stat-icon icon-blue"><i class="bi bi-box-seam"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Available</div><h2>{{ $availableCount }}</h2><div class="stat-sub">not assigned to anyone</div></div>
        <div class="stat-icon icon-green"><i class="bi bi-check-circle"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Assigned</div><h2>{{ $assignedCount }}</h2><div class="stat-sub">with a custodian</div></div>
        <div class="stat-icon icon-orange"><i class="bi bi-person-check"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Maintenance</div><h2>{{ $maintenanceCount }}</h2><div class="stat-sub">being serviced</div></div>
        <div class="stat-icon icon-red"><i class="bi bi-tools"></i></div>
    </div></div>
</div>

<div class="card content-card p-4">
    <form method="GET" action="{{ route('assets.index') }}" class="row g-3 mb-3 align-items-end" id="ovFilters">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <div class="col-md-6 col-xl-4">
            <label class="form-label" for="assetSearch">Search Asset</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" id="assetSearch" name="search" value="{{ $search }}" class="form-control" placeholder="Search code, name, PIC...">
            </div>
        </div>
        <div class="col-md-6 col-xl-2">
            <label class="form-label" for="filterCategory">Category</label>
            <select id="filterCategory" name="category" class="form-select" onchange="this.form.requestSubmit()">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected($selectedCategory == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-xl-2">
            <label class="form-label" for="filterLocation">Location</label>
            <select id="filterLocation" name="location" class="form-select" onchange="this.form.requestSubmit()">
                <option value="">All Locations</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}" @selected($selectedLocation == $location->id)>{{ $location->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-xl-2">
            <label class="form-label" for="filterStatus">Status</label>
            <select id="filterStatus" name="status" class="form-select" onchange="this.form.requestSubmit()">
                <option value="">All Statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}" @selected($selectedStatus == $status->id)>{{ $status->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-xl-2">
            <label class="form-label" for="filterDepartment">Department</label>
            <select id="filterDepartment" name="department" class="form-select" onchange="this.form.requestSubmit()">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department }}" @selected($selectedDepartment === $department)>{{ $department }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <label class="form-label" for="purchaseFrom">Purchase From</label>
            <input type="date" id="purchaseFrom" name="purchase_from" value="{{ $purchaseFrom }}" class="form-control" onchange="this.form.requestSubmit()">
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <label class="form-label" for="purchaseTo">Purchase To</label>
            <input type="date" id="purchaseTo" name="purchase_to" value="{{ $purchaseTo }}" class="form-control" onchange="this.form.requestSubmit()">
        </div>
        <div class="col-md-6 col-xl-8 d-flex gap-2 justify-content-md-end" data-live>
            <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Search</button>
            @if($hasFilters)
                <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-clockwise me-1"></i>Reset</a>
            @endif
        </div>
    </form>

    <div id="dateFilterError" class="text-danger small mb-3 {{ ($purchaseFrom && $purchaseTo && $purchaseFrom > $purchaseTo) ? '' : 'd-none' }}">"Purchase From" cannot be later than "Purchase To".</div>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="fw-bold mb-0">Asset Records</h5>
        <small class="text-muted" data-live>{{ $assets->total() }} asset{{ $assets->total() === 1 ? '' : 's' }} found</small>
    </div>

    {{-- Status chips, sort, export, table / card switch --}}
    @php $query = request()->except(['status', 'page']); @endphp
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3" data-live>
        <div class="chips mb-0">
            <a href="{{ route('assets.index', $query) }}" class="chip {{ $selectedStatus ? '' : 'active' }}">All<b>{{ $totalCount }}</b></a>
            @foreach($statuses as $status)
                <a href="{{ route('assets.index', $query + ['status' => $status->id]) }}" class="chip {{ (string) $selectedStatus === (string) $status->id ? 'active' : '' }}">{{ $status->name }}<b>{{ $status->assets_count }}</b></a>
            @endforeach
        </div>
        <div class="ms-auto d-flex flex-wrap gap-2 align-items-center">
            <select class="form-select" style="width:auto;min-height:40px" aria-label="Sort assets" onchange="var f=document.getElementById('ovFilters');f.sort.value=this.value;f.requestSubmit()">
                @foreach($sorts as $value => $label)
                    <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <a href="{{ request()->fullUrlWithQuery(['export' => 1, 'page' => null]) }}" class="btn btn-secondary"><i class="bi bi-download me-2"></i>Export CSV</a>
            <div class="seg">
                <button type="button" data-view-btn="table" title="Table view"><i class="bi bi-list-ul"></i></button>
                <button type="button" data-view-btn="cards" title="Card view"><i class="bi bi-grid-3x3-gap"></i></button>
            </div>
        </div>
    </div>

    <div data-live>
    @if($assets->isEmpty())
        <div class="empty-state">
            <i class="bi bi-search"></i>
            <h6>No Asset Found</h6>
            <span>Try changing your search or filter option.</span>
        </div>
    @else
        <div class="table-responsive" data-view="table">
            <table class="table table-hover align-middle" data-sortable>
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
                            <td>{{ $asset->custodian->name ?? 'Unassigned' }}@if($asset->custodian)<small class="d-block text-muted">{{ ucwords(str_replace('_', ' ', $asset->custodian->role)) }}</small>@endif</td>
                            <td><span class="badge bg-{{ $asset->assetStatus->badge_color ?? 'secondary' }}">{{ $asset->assetStatus->name ?? 'Unknown' }}</span></td>
                            <td class="text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" title="Quick view" data-bs-toggle="modal" data-bs-target="#assetDetail{{ $asset->id }}"><i class="bi bi-eye"></i></button>
                                @if($canManage)
                                    <a href="{{ route('assets.edit', $asset) }}" class="btn btn-sm btn-outline-secondary me-1" title="Update"><i class="bi bi-pencil-square"></i></a>
                                    <form method="POST" action="{{ route('assets.destroy', $asset) }}" class="d-inline"
                                          data-confirm="Delete asset &quot;{{ $asset->name }}&quot;? This will also remove its photo, assignment, maintenance and issue history. This cannot be undone.">
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

        <div class="ov-grid d-none" data-view="cards">
            @foreach($assets as $asset)
                @php
                    $hue = fn (string $s) => array_reduce(str_split($s ?: 'x'), fn ($h, $c) => ($h * 31 + ord($c)) % 360, 0);
                    $warranty = null;
                    if ($asset->warranty_expiry_date) {
                        $start = $asset->purchase_date ?? $asset->created_at;
                        $total = max(1, $start->diffInDays($asset->warranty_expiry_date));
                        $daysLeft = (int) today()->diffInDays($asset->warranty_expiry_date, false);
                        $warranty = [
                            'pct' => (int) round(min(max($start->diffInDays(today(), false) / $total, 0), 1) * 100),
                            'cls' => $daysLeft < 0 ? 'bad' : ($daysLeft <= 90 ? 'warn' : ''),
                            'label' => $daysLeft < 0 ? 'Expired' : ($daysLeft >= 60 ? round($daysLeft / 30).' mo left' : $daysLeft.' d left'),
                        ];
                    }
                    $category = $asset->category->name ?? '—';
                @endphp
                <article class="ov-card g content-card" tabindex="0" role="button" data-href="{{ route('assets.show', $asset) }}" style="--i:{{ min($loop->index, 10) }};--h:{{ $hue($category) }}">
                    <div class="ov-cover">
                        @if($asset->photo)
                            <img class="asset-thumb" src="{{ $asset->photoUrl() }}" alt="{{ $asset->name }}" loading="lazy" data-lightbox="{{ $asset->photoUrl() }}" data-lb-name="{{ $asset->name }}" data-lb-info="{{ $asset->asset_code }} · {{ $asset->assetStatus->name ?? '-' }}{{ $asset->custodian ? ' · '.$asset->custodian->name : '' }}">
                            <span class="ov-cat">{{ $category }}</span>
                        @else
                            <div class="ov-gen" style="--h:{{ $hue($asset->name) }}"><b>{{ $category }}</b><strong>{{ $asset->name }}</strong><em>{{ $asset->asset_code }}</em></div>
                        @endif
                    </div>
                    <div class="ov-main">
                        <div class="ov-top"><h6>{{ $asset->name }}</h6><span class="badge bg-{{ $asset->assetStatus->badge_color ?? 'secondary' }}">{{ $asset->assetStatus->name ?? 'Unknown' }}</span></div>
                        <div class="ov-sub">{{ $asset->custodian ? $asset->custodian->name.' · '.ucwords(str_replace('_', ' ', $asset->custodian->role)) : 'Unassigned' }}</div>
                        <div class="ov-chips">
                            <span class="pill pill-c">{{ $category }}</span>
                            <span class="pill mono">{{ $asset->asset_code }}</span>
                            <span class="pill"><i class="bi bi-geo-alt"></i>{{ $asset->location->name ?? $asset->location_detail ?? '—' }}</span>
                        </div>
                        <div class="ov-prog">
                            <div class="lbl"><span>Warranty</span><span>{{ $warranty['label'] ?? 'Not set' }}</span></div>
                            <div class="bar {{ $warranty['cls'] ?? '' }}"><i style="--w:{{ $warranty['pct'] ?? 0 }}%"></i></div>
                        </div>
                        @if($canManage)
                            <div class="ov-actions">
                                <a href="{{ route('assets.edit', $asset) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i>Update</a>
                                <form method="POST" action="{{ route('assets.destroy', $asset) }}" class="d-flex flex-fill m-0"
                                      data-confirm="Delete asset &quot;{{ $asset->name }}&quot;? This will also remove its photo, assignment, maintenance and issue history. This cannot be undone.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i>Delete</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2" data-live>
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
                            <img src="{{ $asset->photoUrl() }}" alt="{{ $asset->name }}" class="asset-photo" loading="lazy" data-lightbox="{{ $asset->photoUrl() }}" data-lb-name="{{ $asset->name }}" data-lb-info="{{ $asset->asset_code }}">
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
                        <div class="detail-row"><span class="label">Assigned Date</span><span class="value">{{ optional($asset->assigned_date)->format('d M Y') ?? '—' }}</span></div>
                        <div class="detail-row"><span class="label">Purchase Date</span><span class="value">{{ optional($asset->purchase_date)->format('d M Y') ?? '—' }}</span></div>
                        <div class="detail-row"><span class="label">Supplier</span><span class="value">{{ $asset->supplier ?: '—' }}</span></div>
                        <div class="detail-row"><span class="label">Price</span><span class="value">{{ $asset->purchase_price !== null ? 'RM '.number_format($asset->purchase_price, 2) : '—' }}</span></div>
                        <div class="detail-row"><span class="label">Warranty</span><span class="value">{{ optional($asset->warranty_expiry_date)->format('d M Y') ?? '—' }}</span></div>
                        <div class="detail-row"><span class="label">Description</span><span class="value" style="max-width:60%">{{ $asset->description ?: '—' }}</span></div>
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
                <div class="scan-frame"><video id="qrVideo" width="100%" autoplay muted playsinline></video></div>
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
// Table / card view switch, remembered per browser. The filters refresh the list in place,
// so the views and buttons are looked up each time rather than once.
(function () {
    function show(name) {
        document.querySelectorAll('[data-view]').forEach(function (v) { v.classList.toggle('d-none', v.dataset.view !== name); });
        document.querySelectorAll('[data-view-btn]').forEach(function (b) { b.classList.toggle('active', b.dataset.viewBtn === name); });
    }
    function restore() {
        var saved = 'table';
        try { saved = localStorage.getItem('assetone_ov_view') || 'table'; } catch (e) {}
        show(saved === 'cards' ? 'cards' : 'table');
    }
    restore();
    document.addEventListener('ao:live', restore);

    // A card opens the asset's details page.
    function open(card, e) {
        if (e.target.closest('.ov-actions') || e.target.closest('[data-lightbox]')) return;
        location.href = card.dataset.href;
    }
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-view-btn]');
        if (b) { show(b.dataset.viewBtn); try { localStorage.setItem('assetone_ov_view', b.dataset.viewBtn); } catch (err) {} return; }
        var card = e.target.closest('.ov-card[data-href]');
        if (card) open(card, e);
    });
    document.addEventListener('keydown', function (e) {
        if ((e.key !== 'Enter' && e.key !== ' ') || !e.target.matches || !e.target.matches('.ov-card[data-href]')) return;
        e.preventDefault(); open(e.target, e);
    });

    // Purchase range: warn instead of searching when the dates are the wrong way round.
    var from = document.getElementById('purchaseFrom'), to = document.getElementById('purchaseTo'), err = document.getElementById('dateFilterError');
    [from, to].forEach(function (el) {
        el.removeAttribute('onchange');
        el.onchange = null;
        el.addEventListener('change', function () {
            var bad = from.value && to.value && from.value > to.value;
            err.classList.toggle('d-none', !bad);
            if (!bad) el.form.requestSubmit();
        });
    });
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
        } else if (/^[A-Za-z0-9][A-Za-z0-9-]{2,40}$/.test(data.trim())) {
            // A label that holds only the asset code.
            qrText.innerHTML = "<span class='text-success fw-bold'>Asset code found. Looking it up...</span>";
            scanning = false;
            window.location.href = @json(route('assets.lookup')) + '?code=' + encodeURIComponent(data.trim());
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
