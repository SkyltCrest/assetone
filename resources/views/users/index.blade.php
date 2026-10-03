@extends('layouts.app')

@section('title', 'User Management')
@section('heading', 'User Management')
@section('subheading', 'Manage users and their system access')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">User Management</h3>
        <p class="text-muted mb-0">Manage users and their system access.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="bi bi-person-plus me-2"></i>Add New User
    </button>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Total Users</div><h2>{{ number_format($totalUsers) }}</h2></div>
        <div class="stat-icon icon-blue"><i class="bi bi-people"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Active Users</div><h2>{{ number_format($activeUsers) }}</h2></div>
        <div class="stat-icon icon-green"><i class="bi bi-person-check"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Inactive Users</div><h2>{{ number_format($inactiveUsers) }}</h2></div>
        <div class="stat-icon icon-red"><i class="bi bi-person-x"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Departments</div><h2>{{ number_format($departmentCount) }}</h2></div>
        <div class="stat-icon icon-orange"><i class="bi bi-building"></i></div>
    </div></div>
</div>

<div class="card content-card p-4">
    <form method="GET" action="{{ route('users.index') }}" class="row g-3 mb-4">
        <div class="col-md-12 col-xl-4">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search name, username or email...">
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

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>User</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $i => $user)
                    <tr>
                        <td>{{ $users->firstItem() + $i }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @include('partials.avatar', ['user' => $user])
                                <div>
                                    <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                    <div class="small text-muted">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->username }}</td>
                        <td><span class="badge bg-info">{{ $roles[$user->role] ?? ucwords(str_replace('_', ' ', $user->role)) }}</span></td>
                        <td>{{ $user->department ?: '—' }}</td>
                        <td><span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($user->status) }}</span></td>
                        <td class="text-nowrap">
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            @if($user->id !== auth()->id())
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal{{ $user->id }}" title="Delete"><i class="bi bi-trash"></i></button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">
                        <div class="text-center py-5">
                            <i class="bi bi-people display-4 text-muted"></i>
                            <h5 class="mt-3">No Users Found</h5>
                            <p class="text-muted">Try adjusting your search or filters.</p>
                        </div>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
        <small class="text-muted">Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users</small>
        {{ $users->links() }}
    </div>
</div>

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
