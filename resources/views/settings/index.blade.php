@extends('layouts.app')

@section('title', 'Asset Management')
@section('heading', 'Asset Management')
@section('subheading', 'Manage asset categories, locations and statuses')

@php
    $activeTab = in_array(request()->query('tab'), ['category', 'location', 'status']) ? request()->query('tab') : 'category';
    $badgeColors = ['success' => 'Green (Success)', 'warning' => 'Yellow (Warning)', 'danger' => 'Red (Danger)', 'secondary' => 'Grey (Secondary)', 'info' => 'Blue (Info)', 'dark' => 'Dark Grey'];
@endphp

@section('content')

<x-banner title="Asset Management" text="Create, update and manage asset categories, locations and statuses." :keys="['N' => 'add', '/' => 'search']" />

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Categories</div><h2>{{ $categoryTotal }}</h2><div class="stat-sub">{{ $activeCounts['categories'] }} active</div></div>
        <div class="stat-icon icon-blue"><i class="bi bi-tags"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Asset Types</div><h2>{{ $typeTotal }}</h2><div class="stat-sub">across {{ $categoryTotal }} categories</div></div>
        <div class="stat-icon icon-green"><i class="bi bi-diagram-3"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Locations</div><h2>{{ $locationTotal }}</h2><div class="stat-sub">{{ $activeCounts['locations'] }} active</div></div>
        <div class="stat-icon icon-orange"><i class="bi bi-geo-alt"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Statuses</div><h2>{{ $statusTotal }}</h2><div class="stat-sub">{{ $activeCounts['statuses'] }} active</div></div>
        <div class="stat-icon icon-red"><i class="bi bi-clipboard-check"></i></div>
    </div></div>
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
                <p class="text-muted mb-0">Manage asset categories and their asset types.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'category']) }}" class="btn btn-secondary"><i class="bi bi-download me-2"></i>Export CSV</a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal" data-key="n">
                    <i class="bi bi-plus-lg me-2"></i>Add New Category
                </button>
            </div>
        </div>

        <div class="card content-card p-4">
            <form method="GET" action="{{ route('asset-management.index') }}" class="filter-bar">
                <input type="hidden" name="tab" value="category">
                <div class="search-box flex-grow-1"><i class="bi bi-search"></i><input type="search" name="category_search" value="{{ $categorySearch }}" class="form-control" placeholder="Search category name, code or type..."></div>
                <span class="text-muted ms-auto small"><i class="bi bi-info-circle me-1"></i>{{ $categories->total() }} categor{{ $categories->total() === 1 ? 'y' : 'ies' }} found</span>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle" data-sortable>
                    <thead>
                        <tr>
                            <th>No.</th><th>Category ID</th><th>Code</th><th>Category Name</th><th>Description</th><th>Asset Types</th><th>Total Assets</th><th>Status</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $i => $category)
                            <tr>
                                <td>{{ $categories->firstItem() + $i }}</td>
                                <td class="fw-semibold">{{ $category->code }}</td>
                                <td><span class="code-chip">{{ $category->short_code ?: '—' }}</span></td>
                                <td class="text-dark">{{ $category->name }}</td>
                                <td>{{ $category->description ?: 'No description' }}</td>
                                <td>
                                    @forelse($category->types as $type)
                                        <span class="type-chip">{{ $type->name }} <b>{{ $type->code }}</b></span>
                                    @empty
                                        <span class="text-muted small">No types yet</span>
                                    @endforelse
                                </td>
                                <td>{{ $category->assets_count }}</td>
                                <td><span class="badge bg-{{ $category->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($category->status) }}</span></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('categories.destroy', $category) }}" class="d-inline" data-confirm="Delete category &quot;{{ $category->name }}&quot;?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted py-4">No categories found.</td></tr>
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
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'location']) }}" class="btn btn-secondary"><i class="bi bi-download me-2"></i>Export CSV</a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLocationModal" data-key="n">
                    <i class="bi bi-plus-lg me-2"></i>Add New Location
                </button>
            </div>
        </div>

        <div class="card content-card p-4">
            <form method="GET" action="{{ route('asset-management.index') }}" class="filter-bar">
                <input type="hidden" name="tab" value="location">
                <div class="search-box flex-grow-1"><i class="bi bi-search"></i><input type="search" name="location_search" value="{{ $locationSearch }}" class="form-control" placeholder="Search location name or code..."></div>
                <select name="location_department" class="form-select" onchange="this.form.submit()" aria-label="Filter by department">
                    <option value="">All Departments</option>
                    @foreach(config('assetone.departments') as $department)
                        <option value="{{ $department }}" @selected($locationDepartment === $department)>{{ $department }}</option>
                    @endforeach
                </select>
                <select name="location_status" class="form-select" onchange="this.form.submit()" aria-label="Filter by status">
                    <option value="">All Status</option>
                    <option value="active" @selected($locationStatus === 'active')>Active</option>
                    <option value="inactive" @selected($locationStatus === 'inactive')>Inactive</option>
                </select>
                <span class="text-muted ms-auto small"><i class="bi bi-info-circle me-1"></i>{{ $locations->total() }} location(s) found</span>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle" data-sortable>
                    <thead>
                        <tr>
                            <th>No.</th><th>Code</th><th>Location Name</th><th>Department</th><th>Building / Floor / Room</th><th>Total Assets</th><th>Status</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locations as $i => $location)
                            <tr>
                                <td>{{ $locations->firstItem() + $i }}</td>
                                <td><span class="badge-location-code">{{ $location->code }}</span></td>
                                <td class="text-dark">{{ $location->name }}</td>
                                <td>{{ $location->department }}</td>
                                <td>{{ $location->place() ?: '—' }}</td>
                                <td>{{ $location->assets_count }}</td>
                                <td><span class="badge bg-{{ $location->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($location->status) }}</span></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editLocationModal{{ $location->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" action="{{ route('locations.destroy', $location) }}" class="d-inline" data-confirm="Delete location &quot;{{ $location->name }}&quot;?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No locations found.</td></tr>
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
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'status']) }}" class="btn btn-secondary"><i class="bi bi-download me-2"></i>Export CSV</a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStatusModal" data-key="n">
                    <i class="bi bi-plus-lg me-2"></i>Add New Status
                </button>
            </div>
        </div>

        <div class="card content-card p-4">
            <form method="GET" action="{{ route('asset-management.index') }}" class="filter-bar">
                <input type="hidden" name="tab" value="status">
                <div class="search-box flex-grow-1"><i class="bi bi-search"></i><input type="search" name="status_search" value="{{ $statusSearch }}" class="form-control" placeholder="Search status..."></div>
                <span class="text-muted ms-auto small"><i class="bi bi-info-circle me-1"></i>{{ $statuses->total() }} status(es) found</span>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle" data-sortable>
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
                                    <form method="POST" action="{{ route('statuses.destroy', $assetStatus) }}" class="d-inline" data-confirm="Delete status &quot;{{ $assetStatus->name }}&quot;?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
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

{{-- Edit category pop-ups --}}
@foreach($categories as $category)
    <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('categories.update', $category) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        @include('settings._category-fields', ['category' => $category])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

{{-- Edit location pop-ups --}}
@foreach($locations as $location)
    <div class="modal fade" id="editLocationModal{{ $location->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Location</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('locations.update', $location) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        @include('settings._location-fields', ['location' => $location])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

{{-- Edit status pop-ups --}}
@foreach($statuses as $assetStatus)
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
@endforeach

{{-- Add Category Modal --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('categories.store') }}">
                @csrf
                <div class="modal-body">
                    @include('settings._category-fields', ['category' => null])
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
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('locations.store') }}">
                @csrf
                <div class="modal-body">
                    @include('settings._location-fields', ['location' => null])
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
    // Asset type rows inside the category pop-ups: add / remove, with stable field indexes.
    document.querySelectorAll('[data-types-editor]').forEach(function (editor) {
        var list = editor.querySelector('[data-types-list]');
        var next = list.querySelectorAll('[data-type-row]').length;

        function addRow() {
            var i = next++;
            var row = document.createElement('div');
            row.className = 'row g-2 mb-2 align-items-center';
            row.setAttribute('data-type-row', '');
            row.innerHTML =
                '<div class="col-7"><input type="text" name="types[' + i + '][name]" class="form-control" placeholder="Type name (e.g. Laptop)" required></div>' +
                '<div class="col-3"><input type="text" name="types[' + i + '][code]" class="form-control text-uppercase font-monospace fw-semibold" placeholder="Code" maxlength="5" pattern="[A-Za-z]{1,5}" title="1 to 5 letters" required></div>' +
                '<div class="col-2"><button type="button" class="btn btn-outline-danger w-100" data-type-remove title="Remove type"><i class="bi bi-x-lg"></i></button></div>';
            list.appendChild(row);
            row.querySelector('input').focus();
        }

        editor.querySelector('[data-type-add]').addEventListener('click', addRow);
        list.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-type-remove]');
            if (btn && !btn.disabled) btn.closest('[data-type-row]').remove();
        });
        if (!next) addRow();
    });

    // Remember the last tab, and slide a highlight between the tabs.
    (function () {
        var tabs = document.getElementById('moduleTabs'), KEY = 'assetone_mgmt_tab';
        var glide = document.createElement('li'); glide.className = 'tab-glide'; glide.setAttribute('aria-hidden', 'true');
        tabs.prepend(glide); tabs.classList.add('has-glide');
        function move(a) {
            a = a || tabs.querySelector('.nav-link.active'); if (!a) return;
            var li = a.parentElement;
            // The tabs wrap onto more rows on a narrow screen, so follow the tab's row as well as its column.
            glide.style.width = li.offsetWidth + 'px'; glide.style.height = li.offsetHeight + 'px'; glide.style.bottom = 'auto';
            glide.style.transform = 'translate(' + li.offsetLeft + 'px,' + li.offsetTop + 'px)';
        }
        glide.style.transition = 'none'; move();
        requestAnimationFrame(function () { requestAnimationFrame(function () { glide.style.transition = ''; }); });
        tabs.addEventListener('show.bs.tab', function (e) { move(e.target); });
        addEventListener('resize', function () { move(); });
        if (document.fonts) document.fonts.ready.then(function () { move(); });

        tabs.addEventListener('shown.bs.tab', function (e) { try { localStorage.setItem(KEY, e.target.dataset.tabName); } catch (err) {} });
        if (!new URL(location.href).searchParams.get('tab')) {
            var saved = null; try { saved = localStorage.getItem(KEY); } catch (err) {}
            var btn = saved && tabs.querySelector('[data-tab-name="' + saved + '"]');
            if (btn) bootstrap.Tab.getOrCreateInstance(btn).show();
        }
    })();

    document.getElementById('moduleTabs').addEventListener('shown.bs.tab', function (event) {
        const tabName = event.target.dataset.tabName;
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
    });
</script>
@endpush
