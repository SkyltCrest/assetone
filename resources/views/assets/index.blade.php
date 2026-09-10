@extends('layouts.app')

@section('title', 'Search & Filter')
@section('heading', 'Search & Filter')
@section('subheading', 'Find and filter registered assets')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Asset Search &amp; Filter</h3>
        <p class="text-muted mb-0">Search across all registered assets by name, code, category, location, department or status.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#qrScanModal">
            <i class="bi bi-qr-code-scan me-2"></i>Scan QR
        </button>
        @if(auth()->user()->canManageAssets())
            <a href="{{ route('assets.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Register New Asset</a>
        @endif
    </div>
</div>

<div class="card content-card p-4">
    <form method="GET" action="{{ route('assets.index') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by asset code or name...">
            </div>
        </div>
        <div class="col-md-2">
            <select name="category" class="form-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected($selectedCategory == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="location" class="form-select" onchange="this.form.submit()">
                <option value="">All Locations</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}" @selected($selectedLocation == $location->id)>{{ $location->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}" @selected($selectedStatus == $status->id)>{{ $status->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="department" class="form-select" onchange="this.form.submit()">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department }}" @selected($selectedDepartment === $department)>{{ $department }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Asset ID</th>
                    <th>Asset Name</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Department</th>
                    <th>Custodian</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $asset)
                    <tr>
                        <td class="fw-semibold"><a href="{{ route('assets.show', $asset) }}">{{ $asset->asset_code }}</a></td>
                        <td>{{ $asset->name }}</td>
                        <td>{{ $asset->category->name ?? '—' }}</td>
                        <td>{{ $asset->location->name ?? $asset->location_detail ?? '—' }}</td>
                        <td>{{ $asset->department ?? '—' }}</td>
                        <td>{{ $asset->custodian->name ?? 'Unassigned' }}</td>
                        <td><span class="badge bg-{{ $asset->assetStatus->badge_color ?? 'secondary' }}">{{ $asset->assetStatus->name ?? 'Unknown' }}</span></td>
                        <td>
                            <a href="{{ route('assets.show', $asset) }}" class="btn btn-sm btn-outline-primary me-1" title="View"><i class="bi bi-eye"></i></a>
                            @if(auth()->user()->canManageAssets())
                                <a href="{{ route('assets.edit', $asset) }}" class="btn btn-sm btn-outline-secondary me-1" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('assets.destroy', $asset) }}" class="d-inline"
                                      onsubmit="return confirm('Delete asset &quot;{{ $asset->name }}&quot;? This will also remove its assignment, maintenance and issue history. This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8">
                        <div class="text-center py-5">
                            <i class="bi bi-search display-4 text-muted"></i>
                            <h5 class="mt-3">No Assets Found</h5>
                            <p class="text-muted">Try adjusting your search or filters.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
        <small class="text-muted">Showing {{ $assets->firstItem() ?? 0 }} to {{ $assets->lastItem() ?? 0 }} of {{ $assets->total() }} assets</small>
        {{ $assets->links() }}
    </div>
</div>

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
