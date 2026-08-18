@extends('layouts.app')

@section('title', 'Asset Management')
@section('heading', 'Asset Management')
@section('subheading', 'Manage asset categories, locations and statuses')

@php
    $activeTab = in_array(request()->query('tab'), ['category', 'location', 'status']) ? request()->query('tab') : 'category';
    $badgeColors = ['success' => 'Green (Success)', 'warning' => 'Yellow (Warning)', 'danger' => 'Red (Danger)', 'secondary' => 'Grey (Secondary)', 'info' => 'Blue (Info)', 'dark' => 'Dark Grey'];
@endphp

@push('styles')
<style>
    .module-tabs{ background:#fff; border-radius:var(--card-radius); padding:8px; box-shadow:var(--shadow); }
    .module-tabs .nav-link{ color:var(--muted); font-weight:600; border-radius:10px; padding:12px 20px; }
    .module-tabs .nav-link.active{ background:var(--blue); color:#fff; }
</style>
@endpush

@section('content')

<div class="mb-4">
    <h3 class="fw-bold mb-1">Asset Management Settings</h3>
    <p class="text-muted mb-0">Manage asset categories, locations and current asset statuses.</p>
</div>

<div class="module-tabs mb-4">
    <ul class="nav nav-pills nav-fill" id="moduleTabs">
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'category' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#categoryModule" data-tab-name="category">
                <i class="bi bi-tags me-2"></i>Asset Category
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'location' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#locationModule" data-tab-name="location">
                <i class="bi bi-geo-alt me-2"></i>Asset Location
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'status' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#statusModule" data-tab-name="status">
                <i class="bi bi-clipboard-check me-2"></i>Asset Status
            </button>
        </li>
    </ul>
</div>

<div class="tab-content">

    {{-- Asset Category --}}
    <div class="tab-pane fade {{ $activeTab === 'category' ? 'show active' : '' }}" id="categoryModule">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-1">Asset Categories</h5>
                <p class="text-muted mb-0">Create and manage categories for registered assets.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bi bi-plus-circle me-2"></i>Add New Category
            </button>
        </div>

        <div class="card content-card p-4">
            <div class="row mb-4">
                <div class="col-md-5">
                    <form method="GET" action="{{ route('asset-management.index') }}" class="input-group">
                        <input type="hidden" name="tab" value="category">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="category_search" value="{{ $categorySearch }}" class="form-control" placeholder="Search category...">
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No.</th><th>Category ID</th><th>Category Name</th><th>Description</th><th>Total Assets</th><th>Status</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $i => $category)
                            <tr>
                                <td>{{ $categories->firstItem() + $i }}</td>
                                <td class="fw-semibold">{{ $category->code }}</td>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->description ?: 'No description' }}</td>
                                <td>{{ $category->assets_count }}</td>
                                <td><span class="badge bg-{{ $category->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($category->status) }}</span></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('categories.destroy', $category) }}" class="d-inline" onsubmit="return confirm('Delete category &quot;{{ $category->name }}&quot;?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>

                            <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Category</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="{{ route('categories.update', $category) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Category Name <span class="required">*</span></label>
                                                    <input type="text" name="name" value="{{ $category->name }}" class="form-control" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea name="description" class="form-control" rows="3">{{ $category->description }}</textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="active" @selected($category->status === 'active')>Active</option>
                                                        <option value="inactive" @selected($category->status === 'inactive')>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No categories found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                <small class="text-muted">Showing {{ $categories->firstItem() ?? 0 }} to {{ $categories->lastItem() ?? 0 }} of {{ $categories->total() }} categories</small>
                {{ $categories->links() }}
            </div>
        </div>
    </div>

    {{-- Asset Location --}}
    <div class="tab-pane fade {{ $activeTab === 'location' ? 'show active' : '' }}" id="locationModule">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-1">Asset Locations</h5>
                <p class="text-muted mb-0">Create and manage physical locations for registered assets.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal">
                <i class="bi bi-plus-circle me-2"></i>Add New Location
            </button>
        </div>

        <div class="card content-card p-4">
            <div class="row mb-4">
                <div class="col-md-5">
                    <form method="GET" action="{{ route('asset-management.index') }}" class="input-group">
                        <input type="hidden" name="tab" value="location">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="location_search" value="{{ $locationSearch }}" class="form-control" placeholder="Search location...">
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No.</th><th>Location ID</th><th>Location Name</th><th>Department</th><th>Total Assets</th><th>Status</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locations as $i => $location)
                            <tr>
                                <td>{{ $locations->firstItem() + $i }}</td>
                                <td class="fw-semibold">{{ $location->code }}</td>
                                <td>{{ $location->name }}</td>
                                <td>{{ $location->department }}</td>
                                <td>{{ $location->assets_count }}</td>
                                <td><span class="badge bg-{{ $location->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($location->status) }}</span></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editLocationModal{{ $location->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('locations.destroy', $location) }}" class="d-inline" onsubmit="return confirm('Delete location &quot;{{ $location->name }}&quot;?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>

                            <div class="modal fade" id="editLocationModal{{ $location->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Location</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="{{ route('locations.update', $location) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Location Name <span class="required">*</span></label>
                                                    <input type="text" name="name" value="{{ $location->name }}" class="form-control" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Department <span class="required">*</span></label>
                                                    <input type="text" name="department" value="{{ $location->department }}" class="form-control" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea name="description" class="form-control" rows="3">{{ $location->description }}</textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="active" @selected($location->status === 'active')>Active</option>
                                                        <option value="inactive" @selected($location->status === 'inactive')>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No locations found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                <small class="text-muted">Showing {{ $locations->firstItem() ?? 0 }} to {{ $locations->lastItem() ?? 0 }} of {{ $locations->total() }} locations</small>
                {{ $locations->links() }}
            </div>
        </div>
    </div>

    {{-- Asset Status --}}
    <div class="tab-pane fade {{ $activeTab === 'status' ? 'show active' : '' }}" id="statusModule">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-1">Asset Statuses</h5>
                <p class="text-muted mb-0">Create and manage the statuses assets can be assigned.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStatusModal">
                <i class="bi bi-plus-circle me-2"></i>Add New Status
            </button>
        </div>

        <div class="card content-card p-4">
            <div class="row mb-4">
                <div class="col-md-5">
                    <form method="GET" action="{{ route('asset-management.index') }}" class="input-group">
                        <input type="hidden" name="tab" value="status">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="status_search" value="{{ $statusSearch }}" class="form-control" placeholder="Search status...">
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No.</th><th>Status ID</th><th>Status Name</th><th>Description</th><th>Total Assets</th><th>Badge</th><th>Status</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statuses as $i => $assetStatus)
                            <tr>
                                <td>{{ $statuses->firstItem() + $i }}</td>
                                <td class="fw-semibold">{{ $assetStatus->code }}</td>
                                <td>{{ $assetStatus->name }}</td>
                                <td>{{ $assetStatus->description ?: 'No description' }}</td>
                                <td>{{ $assetStatus->assets_count }}</td>
                                <td><span class="badge bg-{{ $assetStatus->badge_color }}">{{ $assetStatus->name }}</span></td>
                                <td><span class="badge bg-{{ $assetStatus->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($assetStatus->status) }}</span></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editStatusModal{{ $assetStatus->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('statuses.destroy', $assetStatus) }}" class="d-inline" onsubmit="return confirm('Delete status &quot;{{ $assetStatus->name }}&quot;?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>

                            <div class="modal fade" id="editStatusModal{{ $assetStatus->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Status</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="{{ route('statuses.update', $assetStatus) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Status Name <span class="required">*</span></label>
                                                    <input type="text" name="name" value="{{ $assetStatus->name }}" class="form-control" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea name="description" class="form-control" rows="3">{{ $assetStatus->description }}</textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Badge Colour <span class="required">*</span></label>
                                                    <select name="badge_color" class="form-select" required>
                                                        @foreach($badgeColors as $value => $label)
                                                            <option value="{{ $value }}" @selected($assetStatus->badge_color === $value)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select">
                                                        <option value="active" @selected($assetStatus->status === 'active')>Active</option>
                                                        <option value="inactive" @selected($assetStatus->status === 'inactive')>Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No statuses found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                <small class="text-muted">Showing {{ $statuses->firstItem() ?? 0 }} to {{ $statuses->lastItem() ?? 0 }} of {{ $statuses->total() }} statuses</small>
                {{ $statuses->links() }}
            </div>
        </div>
    </div>

</div>

{{-- Add Category Modal --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('categories.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="required">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Enter category name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Enter category description">{{ old('description') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Location Modal --}}
<div class="modal fade" id="addLocationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('locations.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Location Name <span class="required">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Enter location name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department <span class="required">*</span></label>
                        <input type="text" name="department" value="{{ old('department') }}" class="form-control" placeholder="Enter department" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Enter location description">{{ old('description') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Location</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Status Modal --}}
<div class="modal fade" id="addStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('statuses.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status Name <span class="required">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Example: In Use" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Enter status description">{{ old('description') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Badge Colour <span class="required">*</span></label>
                        <select name="badge_color" class="form-select" required>
                            <option value="" selected disabled>Select badge colour</option>
                            @foreach($badgeColors as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.getElementById('moduleTabs').addEventListener('shown.bs.tab', function (event) {
        const tabName = event.target.dataset.tabName;
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
    });
</script>
@endpush
