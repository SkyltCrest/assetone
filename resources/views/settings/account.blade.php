@extends('layouts.app')

@section('title', 'Settings')
@section('heading', 'Settings')
@section('subheading', 'Manage your account information')

@section('content')

<div class="mb-4">
    <h3 class="fw-bold mb-1">Account Settings</h3>
    <p class="text-muted mb-0">Update your profile information and password.</p>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card content-card p-4 text-center">
            <div class="avatar-badge mx-auto mb-3" style="width:72px;height:72px;font-size:1.8rem;">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <h5 class="fw-bold mb-0">{{ $user->name }}</h5>
            <p class="text-muted mb-0">{{ ucwords(str_replace('_', ' ', $user->role)) }}</p>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card content-card p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-person-badge me-2"></i>Profile Information</h5>
            <form method="POST" action="{{ route('settings.profile.update') }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Full Name <span class="required">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Email <span class="required">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Username</label>
                        <input type="text" value="{{ $user->username }}" class="form-control" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Role</label>
                        <input type="text" value="{{ ucwords(str_replace('_', ' ', $user->role)) }}" class="form-control" disabled>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-save px-4"><i class="bi bi-save me-2"></i>Save Changes</button>
                </div>
            </form>
        </div>

        <div class="card content-card p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-shield-lock me-2"></i>Change Password</h5>
            <form method="POST" action="{{ route('settings.password.update') }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-medium">Current Password <span class="required">*</span></label>
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">New Password <span class="required">*</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" minlength="8" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Confirm New Password <span class="required">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" minlength="8" required>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-save px-4"><i class="bi bi-key me-2"></i>Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
