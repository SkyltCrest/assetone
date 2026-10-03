{{-- Shared asset form fields --}}

@php
    $selectedCategory = (int) old('asset_category_id', $asset->asset_category_id);
    $selectedType = (int) old('asset_type_id', $asset->asset_type_id);
    $currentDepartment = old('department', $asset->department);
    // Types per category, for the dependent "Asset Type" dropdown.
    $typesByCategory = $categories->mapWithKeys(fn ($c) => [$c->id => $c->types->map->only(['id', 'name', 'code'])->values()]);
@endphp

<div class="card form-card p-4 mb-4">
    <h5 class="section-title mb-4"><i class="bi bi-box-seam me-2"></i>Basic Asset Information</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="assetName">Asset Name <span class="required">*</span></label>
            <input type="text" id="assetName" name="name" value="{{ old('name', $asset->name) }}" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Dell Latitude 5420" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="assetCategory">Asset Category <span class="required">*</span></label>
            <select id="assetCategory" name="asset_category_id" class="form-select @error('asset_category_id') is-invalid @enderror" required>
                <option value="" @selected(! $selectedCategory) disabled>Select category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected($selectedCategory === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('asset_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="assetType">Asset Type <span class="required">*</span></label>
            <select id="assetType" name="asset_type_id" class="form-select @error('asset_type_id') is-invalid @enderror" data-selected="{{ $selectedType ?: '' }}" required>
                <option value="">Select category first</option>
            </select>
            @error('asset_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Asset types are managed per category in Asset Management.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="assetCode">Asset Code</label>
            <input type="text" id="assetCode" class="form-control" value="{{ $asset->asset_code }}" placeholder="Auto generated" data-current="{{ $asset->asset_code }}" readonly>
            <div class="form-text">Auto-generated from Category Code, Type Code, Year &amp; Running No.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="serialNumber">Serial Number <span class="required">*</span></label>
            <input type="text" id="serialNumber" name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" class="form-control @error('serial_number') is-invalid @enderror" placeholder="e.g. SN-5CG1234XYZ" required>
            @error('serial_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            @if($asset->exists)
                <label class="form-label" for="assetStatus">Asset Status <span class="required">*</span></label>
                <select id="assetStatus" name="asset_status_id" class="form-select @error('asset_status_id') is-invalid @enderror" required>
                    <option value="" @selected(! old('asset_status_id', $asset->asset_status_id)) disabled>Select status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" @selected((int) old('asset_status_id', $asset->asset_status_id) === $status->id)>{{ $status->name }}</option>
                    @endforeach
                </select>
                @error('asset_status_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            @else
                <label class="form-label">Asset Status</label>
                <input type="text" class="form-control" value="Active" disabled>
                <div class="form-text">New assets are registered as Active automatically.</div>
            @endif
        </div>
        <div class="col-12">
            <label class="form-label" for="assetDescription">Description</label>
            <textarea id="assetDescription" name="description" class="form-control" rows="3" placeholder="Enter asset description">{{ old('description', $asset->description) }}</textarea>
        </div>
    </div>
</div>

<div class="card form-card p-4 mb-4">
    <h5 class="section-title mb-4"><i class="bi bi-image me-2"></i>Asset Photo <span class="required ms-1">*</span></h5>
    @include('partials.photo-picker', [
        'name' => 'photo',
        'current' => $asset->photoUrl(),
        'required' => ! $asset->photo,
    ])
    <div class="form-text mt-2">Upload a picture or take one with the camera. {{ $asset->photo ? 'Leave as is to keep the current photo.' : '' }}</div>
</div>

<div class="card form-card p-4 mb-4">
    <h5 class="section-title mb-4"><i class="bi bi-cart-check me-2"></i>Purchase Information</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="purchaseDate">Purchase Date <span class="required">*</span></label>
            <input type="date" id="purchaseDate" name="purchase_date" value="{{ old('purchase_date', optional($asset->purchase_date)->format('Y-m-d')) }}" class="form-control @error('purchase_date') is-invalid @enderror" max="{{ today()->format('Y-m-d') }}" required>
            @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="purchasePrice">Purchase Price (RM) <span class="required">*</span></label>
            <input type="number" id="purchasePrice" name="purchase_price" value="{{ old('purchase_price', $asset->purchase_price) }}" class="form-control @error('purchase_price') is-invalid @enderror" placeholder="e.g. 2500.00" step="0.01" min="0" required>
            @error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="supplier">Supplier</label>
            <input type="text" id="supplier" name="supplier" value="{{ old('supplier', $asset->supplier) }}" class="form-control" placeholder="Enter supplier name">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="poReference">Purchase Order / Reference No.</label>
            <input type="text" id="poReference" name="po_reference" value="{{ old('po_reference', $asset->po_reference) }}" class="form-control" placeholder="e.g. PO-2026-0153">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="warrantyDate">Warranty Expiry Date</label>
            <input type="date" id="warrantyDate" name="warranty_expiry_date" value="{{ old('warranty_expiry_date', optional($asset->warranty_expiry_date)->format('Y-m-d')) }}" class="form-control @error('warranty_expiry_date') is-invalid @enderror">
            @error('warranty_expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="card form-card p-4 mb-4">
    <h5 class="section-title mb-4"><i class="bi bi-geo-alt me-2"></i>Asset Location</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="department">Department <span class="required">*</span></label>
            <select id="department" name="department" class="form-select @error('department') is-invalid @enderror" required>
                <option value="" @selected(! $currentDepartment) disabled>Select department</option>
                @foreach($departments as $department)
                    <option value="{{ $department }}" @selected($currentDepartment === $department)>{{ $department }}</option>
                @endforeach
                @if($currentDepartment && ! in_array($currentDepartment, $departments, true))
                    <option value="{{ $currentDepartment }}" selected>{{ $currentDepartment }}</option>
                @endif
            </select>
            @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="assetLocation">Asset Location</label>
            <select id="assetLocation" name="asset_location_id" class="form-select">
                <option value="">Not linked to a location record</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}" data-place="{{ trim($location->name.($location->place() ? ', '.$location->place() : '')) }}" @selected((int) old('asset_location_id', $asset->asset_location_id) === $location->id)>{{ $location->code }} — {{ $location->name }}</option>
                @endforeach
            </select>
            <div class="form-text">Locations are managed in Asset Management.</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="locationDetail">Location Details <span class="required">*</span></label>
            <input type="text" id="locationDetail" name="location_detail" value="{{ old('location_detail', $asset->location_detail) }}" class="form-control @error('location_detail') is-invalid @enderror" placeholder="e.g. Level 2, IT Department" required>
            @error('location_detail')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="card form-card p-4 mb-4">
    <h5 class="section-title mb-4"><i class="bi bi-person-check me-2"></i>Asset Custodian</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="custodian">Person in Charge (PIC)</label>
            <select id="custodian" name="custodian_id" class="form-select">
                <option value="">No custodian yet</option>
                @foreach($custodians as $custodian)
                    <option value="{{ $custodian->id }}" @selected((int) old('custodian_id', $asset->custodian_id) === $custodian->id)>{{ $custodian->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="assignedDate">Assigned Date</label>
            <input type="date" id="assignedDate" name="assigned_date" value="{{ old('assigned_date', optional($asset->assigned_date)->format('Y-m-d')) }}" class="form-control @error('assigned_date') is-invalid @enderror">
            @error('assigned_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var typesByCategory = @json($typesByCategory);
    var category = document.getElementById('assetCategory'),
        type = document.getElementById('assetType'),
        code = document.getElementById('assetCode'),
        purchase = document.getElementById('purchaseDate'),
        location = document.getElementById('assetLocation'),
        locationDetail = document.getElementById('locationDetail'),
        custodian = document.getElementById('custodian'),
        assigned = document.getElementById('assignedDate');
    var current = code.dataset.current;
    var previewUrl = @json(route('assets.next-code'));

    function fillTypes() {
        var list = typesByCategory[category.value] || [], wanted = type.dataset.selected;
        type.innerHTML = '';
        type.add(new Option(category.value ? (list.length ? 'Select type' : 'No types for this category yet') : 'Select category first', ''));
        list.forEach(function (t) {
            type.add(new Option(t.name + ' (' + t.code + ')', t.id, false, String(t.id) === String(wanted)));
        });
        type.disabled = !list.length;
    }

    // Preview the code the asset will get. The final number is assigned on save.
    var isGenerated = /^[A-Z0-9]+-[A-Z0-9]+-\d{4}-\d{3,}$/;
    function previewCode() {
        if (current && !isGenerated.test(current)) { code.value = current; return; }   // older codes are kept
        if (!type.value || !purchase.value) { code.value = current || ''; return; }
        fetch(previewUrl + '?type=' + encodeURIComponent(type.value) + '&purchase_date=' + encodeURIComponent(purchase.value), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data) return;
                var prefix = data.code.replace(/\d+$/, '');
                code.value = current && current.indexOf(prefix) === 0 ? current : data.code;
            })
            .catch(function () {});
    }

    category.addEventListener('change', function () { type.dataset.selected = ''; fillTypes(); previewCode(); });
    type.addEventListener('change', function () { type.dataset.selected = type.value; previewCode(); });
    purchase.addEventListener('change', previewCode);

    // Picking a location record fills in the free-text details when they are empty.
    location.addEventListener('change', function () {
        var place = location.selectedOptions[0] ? location.selectedOptions[0].dataset.place : '';
        if (place && !locationDetail.value.trim()) locationDetail.value = place;
    });

    // Choosing a custodian defaults the assigned date to today.
    custodian.addEventListener('change', function () {
        if (custodian.value && !assigned.value) assigned.value = new Date().toISOString().slice(0, 10);
        if (!custodian.value) assigned.value = '';
    });

    fillTypes();
    previewCode();
})();
</script>
@endpush
