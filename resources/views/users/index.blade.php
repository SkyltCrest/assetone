@extends('layouts.app')

@section('title', 'User Management')
@section('heading', 'User Management')
@section('subheading', 'Manage users and their system access')

@php
    $roles = ['administrator' => 'Administrator', 'asset_officer' => 'Asset Officer', 'department_staff' => 'Department Staff'];
@endphp

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

<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted mb-1">Total Users</p>
                <h3 class="mb-0">{{ number_format($totalUsers) }}</h3>
            </div>
            <div class="stat-icon icon-blue"><i class="bi bi-people"></i></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted mb-1">Active Users</p>
                <h3 class="mb-0">{{ number_format($activeUsers) }}</h3>
            </div>
            <div class="stat-icon icon-green"><i class="bi bi-person-check"></i></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted mb-1">Inactive Users</p>
                <h3 class="mb-0">{{ number_format($inactiveUsers) }}</h3>
            </div>
            <div class="stat-icon icon-red"><i class="bi bi-person-x"></i></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted mb-1">Administrators</p>
                <h3 class="mb-0">{{ number_format($adminUsers) }}</h3>
            </div>
            <div class="stat-icon icon-orange"><i class="bi bi-shield-check"></i></div>
        </div>
    </div>
</div>

<div class="card content-card p-4">
    <form method="GET" action="{{ route('users.index') }}" class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search user...">
            </div>
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select" onchange="this.form.submit()">
                <option value="">All Roles</option>
                @foreach($roles as $value => $label)
                    <option value="{{ $value }}" @selected($role === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $i => $user)
                    <tr>
                        <td>{{ $users->firstItem() + $i }}</td>
                        <td class="fw-semibold">{{ $user->name }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge bg-info">{{ $roles[$user->role] ?? ucwords(str_replace('_', ' ', $user->role)) }}</span></td>
                        <td><span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($user->status) }}</span></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Delete user &quot;{{ $user->name }}&quot;?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>

                    <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit User</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="{{ route('users.update', $user) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Full Name <span class="required">*</span></label>
                                                <input type="text" name="name" value="{{ $user->name }}" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Username <span class="required">*</span></label>
                                                <input type="text" name="username" value="{{ $user->username }}" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Email <span class="required">*</span></label>
                                                <input type="email" name="email" value="{{ $user->email }}" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">New Password</label>
                                                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Role <span class="required">*</span></label>
                                                <select name="role" class="form-select" required>
                                                    @foreach($roles as $value => $label)
                                                        <option value="{{ $value }}" @selected($user->role === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Account Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="active" @selected($user->status === 'active')>Active</option>
                                                    <option value="inactive" @selected($user->status === 'inactive')>Inactive</option>
                                                </select>
                                            </div>
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

<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="required">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Enter full name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Username <span class="required">*</span></label>
                            <input type="text" name="username" value="{{ old('username') }}" class="form-control" placeholder="Enter username" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="required">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Enter email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="required">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role <span class="required">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="" selected disabled>Select role</option>
                                @foreach($roles as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Account Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
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
