{{-- Shared fields for Register / Edit Asset forms. Expects $asset, $categories, $departments, $custodians, $statuses --}}

<div class="card form-card p-4 mb-4">
    <h5 class="section-title mb-4"><i class="bi bi-info-circle me-2"></i>Basic Asset Information</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Asset Name <span class="required">*</span></label>
            <input type="text" name="name" value="{{ old('name', $asset->name) }}" class="form-control @error('name') is-invalid @enderror" placeholder="Enter asset name" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Asset Code / ID</label>
            <input type="text" class="form-control" value="{{ $asset->exists ? $asset->asset_code : 'Auto-generated on save' }}" disabled>
        </div>
        <div class="col-md-6">
            <label class="form-label">Asset Category <span class="required">*</span></label>
            <select name="asset_category_id" class="form-select @error('asset_category_id') is-invalid @enderror" required>
                <option value="" @selected(!old('asset_category_id', $asset->asset_category_id)) disabled>Select category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) old('asset_category_id', $asset->asset_category_id) === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('asset_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" placeholder="Enter asset description">{{ old('description', $asset->description) }}</textarea>
        </div>
    </div>
</div>

<div class="card form-card p-4 mb-4">
    <h5 class="section-title mb-4"><i class="bi bi-cart-check me-2"></i>Purchase Information</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Purchase Date</label>
            <input type="date" name="purchase_date" value="{{ old('purchase_date', optional($asset->purchase_date)->format('Y-m-d')) }}" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Purchase Price (RM)</label>
            <input type="number" name="purchase_price" value="{{ old('purchase_price', $asset->purchase_price) }}" class="form-control" placeholder="Example: 2500.00" step="0.01" min="0">
        </div>
        <div class="col-md-6">
            <label class="form-label">Supplier</label>
            <input type="text" name="supplier" value="{{ old('supplier', $asset->supplier) }}" class="form-control" placeholder="Enter supplier name">
        </div>
        <div class="col-md-6">
            <label class="form-label">Warranty Expiry Date</label>
            <input type="date" name="warranty_expiry_date" value="{{ old('warranty_expiry_date', optional($asset->warranty_expiry_date)->format('Y-m-d')) }}" class="form-control">
        </div>
    </div>
</div>

<div class="card form-card p-4 mb-4">
    <h5 class="section-title mb-4"><i class="bi bi-geo-alt me-2"></i>Asset Location</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Department <span class="required">*</span></label>
            <select name="department" class="form-select @error('department') is-invalid @enderror" required>
                <option value="" @selected(!old('department', $asset->department)) disabled>Select department</option>
                @foreach($departments as $department)
                    <option value="{{ $department }}" @selected(old('department', $asset->department) === $department)>{{ $department }}</option>
                @endforeach
            </select>
            @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Location <span class="required">*</span></label>
            <input type="text" name="location_detail" value="{{ old('location_detail', $asset->location_detail) }}" class="form-control @error('location_detail') is-invalid @enderror" placeholder="Example: Level 2, IT Department" required>
            @error('location_detail')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label">Linked Location Record</label>
            <select name="asset_location_id" class="form-select">
                <option value="">None</option>
                @foreach(\App\Models\AssetLocation::where('status', 'active')->orderBy('name')->get() as $location)
                    <option value="{{ $location->id }}" @selected((int) old('asset_location_id', $asset->asset_location_id) === $location->id)>{{ $location->code }} — {{ $location->name }}</option>
                @endforeach
            </select>
            <small class="text-muted">Optional — link this asset to a managed location record for tracking.</small>
        </div>
    </div>
</div>

<div class="card form-card p-4 mb-4">
    <h5 class="section-title mb-4"><i class="bi bi-person-check me-2"></i>Assignment &amp; Asset Status</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Person in Charge (PIC)</label>
            <select name="custodian_id" class="form-select">
                <option value="">Select custodian</option>
                @foreach($custodians as $custodian)
                    <option value="{{ $custodian->id }}" @selected((int) old('custodian_id', $asset->custodian_id) === $custodian->id)>{{ $custodian->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Asset Status <span class="required">*</span></label>
            <select name="asset_status_id" class="form-select @error('asset_status_id') is-invalid @enderror" required>
                <option value="" @selected(!old('asset_status_id', $asset->asset_status_id)) disabled>Select status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}" @selected((int) old('asset_status_id', $asset->asset_status_id) === $status->id)>{{ $status->name }}</option>
                @endforeach
            </select>
            @error('asset_status_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
