@extends('layouts.app')

@section('title', 'User Management')
@section('heading', 'User Management')
@section('subheading', 'Manage users and their system access')

@section('content')

<x-banner title="User Management" text="Create and manage users who can access AssetOne." :keys="['N' => 'new', '/' => 'search']">
    <a href="{{ request()->fullUrlWithQuery(['export' => 1, 'page' => null]) }}" class="btn btn-secondary"><i class="bi bi-download me-2"></i>Export CSV</a>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal" data-key="n">
        <i class="bi bi-person-plus me-2"></i>Add New User
    </button>
</x-banner>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Total Users</div><h2>{{ number_format($totalUsers) }}</h2><div class="stat-sub">registered accounts</div></div>
        <div class="stat-icon icon-blue"><i class="bi bi-people"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Active Users</div><h2>{{ number_format($activeUsers) }}</h2><div class="stat-sub">can sign in</div></div>
        <div class="stat-icon icon-green"><i class="bi bi-person-check"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Inactive Users</div><h2>{{ number_format($inactiveUsers) }}</h2><div class="stat-sub">access switched off</div></div>
        <div class="stat-icon icon-red"><i class="bi bi-person-x"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Departments</div><h2>{{ number_format($departmentCount) }}</h2><div class="stat-sub">with at least one user</div></div>
        <div class="stat-icon icon-orange"><i class="bi bi-building"></i></div>
    </div></div>
</div>

<div class="card content-card p-4">
    <form method="GET" action="{{ route('users.index') }}" class="row g-3 mb-4">
        <div class="col-md-12 col-xl-4">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search members...">
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <select name="role" class="form-select" onchange="this.form.submit()" aria-label="Filter by role">
                <option value="">All Roles</option>
                @foreach($roles as $value => $label)
                    <option value="{{ $value }}" @selected($role === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-xl-2">
            <select name="department" class="form-select" onchange="this.form.submit()" aria-label="Filter by department">
                <option value="">All Departments</option>
                @foreach($departments as $option)
                    <option value="{{ $option }}" @selected($department === $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-xl-2">
            <select name="status" class="form-select" onchange="this.form.submit()" aria-label="Filter by status">
                <option value="">All Status</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="col-sm-6 col-xl-2">
            <select name="sort" class="form-select" onchange="this.form.submit()" aria-label="Sort users">
                @foreach($sorts as $value => $label)
                    <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </form>

    {{-- Member cards --}}
    <div class="card-grid" id="userCards">
        @forelse($users as $i => $user)
            @php
                $on = $user->status === 'active';
                $roleLabel = $roles[$user->role] ?? ucwords(str_replace('_', ' ', $user->role));
                $hue = array_reduce(str_split($roleLabel), fn ($h, $c) => ($h * 31 + ord($c)) % 360, 0);
            @endphp
            <div class="member-card" data-id="{{ $user->id }}" data-profile="#profileModal{{ $user->id }}" style="animation-delay:{{ $i * 40 }}ms" tabindex="0">
                <span class="m-check" role="checkbox" aria-label="Select {{ $user->name }}"><i class="bi bi-check-lg"></i></span>
                <div class="av-wrap">
                    <div class="member-avatar {{ $user->photo ? '' : 'avatar-c'.($user->id % 6) }}">
                        @if($user->photo)<img src="{{ $user->photoUrl() }}" alt="{{ $user->name }}" loading="lazy">@else{{ $user->initials() }}@endif
                    </div>
                    <span class="status-dot {{ $on ? 'on' : '' }}"></span>
                </div>
                <div class="member-name" title="{{ $user->name }}">{{ $user->name }}</div>
                <div class="member-email" title="{{ $user->email }}">{{ $user->email }}</div>
                <div class="member-dept"><i class="bi bi-building"></i>{{ $user->department ? str_replace(' Department', '', $user->department) : '-' }}</div>
                <span class="badge bg-{{ $on ? 'success' : 'secondary' }}">{{ ucfirst($user->status) }}</span>
                <div class="member-meta">
                    <span>{{ $user->username }}</span>
                    <b class="role-pill" style="--h:{{ $hue }}">{{ $roleLabel }}</b>
                </div>
                <div class="member-actions">
                    <button type="button" class="m-edit" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}" title="Edit User"><i class="bi bi-pencil"></i></button>
                    @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('users.toggle', $user) }}" class="d-inline m-0">
                            @csrf
                            <button type="submit" class="{{ $on ? 'm-toggle-on' : 'm-toggle-off' }}" title="{{ $on ? 'Deactivate User' : 'Activate User' }}"><i class="bi {{ $on ? 'bi-person-x' : 'bi-person-check' }}"></i></button>
                        </form>
                        <button type="button" class="m-del" data-bs-toggle="modal" data-bs-target="#deleteUserModal{{ $user->id }}" title="Delete User"><i class="bi bi-trash"></i></button>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state" style="grid-column:1/-1">
                <i class="bi bi-person-x"></i>
                <h6>No users found.</h6>
                <span>Try adjusting your search or filters.</span>
            </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
        <small class="text-muted">Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users</small>
        {{ $users->links() }}
    </div>
</div>

{{-- Bulk actions for selected cards --}}
<form method="POST" action="{{ route('users.bulk-status') }}" class="bulkbar g" id="bulkbar">
    @csrf
    <input type="hidden" name="status" id="bulkStatus">
    <div id="bulkIds"></div>
    <span class="cnt"><span id="bulkN">0</span> selected</span>
    <button type="button" class="btn btn-sm btn-secondary" data-a="all">Select page</button>
    <button type="button" class="btn btn-sm btn-outline-success" data-a="active"><i class="bi bi-person-check me-1"></i>Activate</button>
    <button type="button" class="btn btn-sm btn-outline-warning" data-a="inactive"><i class="bi bi-person-x me-1"></i>Deactivate</button>
    <button type="button" class="btn btn-sm btn-outline-secondary" data-a="clear">Clear</button>
</form>

{{-- Profile quick view --}}
@foreach($users as $user)
    <div class="modal fade" id="profileModal{{ $user->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title"><i class="bi bi-person-badge me-2"></i>User Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="pf-head">
                        <div class="member-avatar {{ $user->photo ? '' : 'avatar-c'.($user->id % 6) }}">
                            @if($user->photo)<img src="{{ $user->photoUrl() }}" alt="">@else{{ $user->initials() }}@endif
                        </div>
                        <div>
                            <h5>{{ $user->name }}</h5>
                            <div class="text-muted small mb-1">{{ $user->username }}</div>
                            <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($user->status) }}</span>
                        </div>
                    </div>
                    <div class="pf-grid">
                        <div><small>Role</small><b>{{ $roles[$user->role] ?? ucwords(str_replace('_', ' ', $user->role)) }}</b></div>
                        <div><small>Department</small><b>{{ $user->department ?: '-' }}</b></div>
                        <div><small>Email</small><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></div>
                        <div><small>Assigned assets</small><b>{{ $user->assets->count() }}</b></div>
                    </div>
                    @forelse($user->assets->take(4) as $n => $held)
                        <div class="pf-asset" style="animation-delay:{{ $n * 60 }}ms">
                            @if($held->photoUrl())<img src="{{ $held->photoUrl() }}" alt="">@else<span class="ph"><i class="bi bi-image"></i></span>@endif
                            <div><b>{{ $held->name }}</b><small>{{ $held->asset_code }}</small></div>
                        </div>
                    @empty
                        <div class="text-muted small">No assets assigned to this user.</div>
                    @endforelse
                    @if($user->assets->count() > 4)
                        <div class="text-muted small mt-1">+{{ $user->assets->count() - 4 }} more</div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}"><i class="bi bi-pencil me-2"></i>Edit User</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

@foreach($users as $user)
    <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data" class="d-flex flex-column overflow-hidden">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        @include('users._fields', ['user' => $user])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($user->id !== auth()->id())
    <div class="modal fade" id="deleteUserModal{{ $user->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Delete the account of <strong>{{ $user->name }}</strong> ({{ $user->email }})? This cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i>Delete User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" class="d-flex flex-column overflow-hidden">
                @csrf
                <div class="modal-body">
                    @include('users._fields', ['user' => null])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Card selection + bulk activate / deactivate, and the profile quick view.
(function () {
    var grid = document.getElementById('userCards'), bar = document.getElementById('bulkbar'), sel = {};
    var me = @json((string) auth()->id());
    document.body.appendChild(bar);   // keep the floating bar out of the glass card's stacking context

    function refresh() {
        var ids = Object.keys(sel);
        document.getElementById('bulkN').textContent = ids.length;
        bar.classList.toggle('show', ids.length > 0);
        grid.classList.toggle('sel-mode', ids.length > 0);
        grid.querySelectorAll('.member-card').forEach(function (c) { c.classList.toggle('selected', !!sel[c.dataset.id]); });
    }
    grid.addEventListener('click', function (e) {
        var card = e.target.closest('.member-card'); if (!card) return;
        if (e.target.closest('.m-check')) { if (sel[card.dataset.id]) delete sel[card.dataset.id]; else sel[card.dataset.id] = 1; refresh(); return; }
        if (e.target.closest('.member-actions')) return;
        if (Object.keys(sel).length) { if (sel[card.dataset.id]) delete sel[card.dataset.id]; else sel[card.dataset.id] = 1; refresh(); return; }
        bootstrap.Modal.getOrCreateInstance(document.querySelector(card.dataset.profile)).show();
    });
    grid.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' || !e.target.classList.contains('member-card')) return;
        bootstrap.Modal.getOrCreateInstance(document.querySelector(e.target.dataset.profile)).show();
    });
    bar.addEventListener('click', function (e) {
        var b = e.target.closest('button'); if (!b) return;
        var a = b.dataset.a;
        if (a === 'clear') { sel = {}; refresh(); return; }
        if (a === 'all') { grid.querySelectorAll('.member-card').forEach(function (c) { sel[c.dataset.id] = 1; }); refresh(); return; }
        var box = document.getElementById('bulkIds'); box.innerHTML = '';
        Object.keys(sel).filter(function (id) { return id !== me; }).forEach(function (id) {
            var i = document.createElement('input'); i.type = 'hidden'; i.name = 'ids[]'; i.value = id; box.appendChild(i);
        });
        if (!box.children.length) { if (window.aoToast) window.aoToast('You cannot change your own status.'); return; }
        document.getElementById('bulkStatus').value = a;
        bar.submit();
    });
})();
</script>
@endpush
