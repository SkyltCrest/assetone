{{-- User form fields, shared by the Add and Edit pop-ups. $user is null when adding. --}}
@php
    $user = $user ?? null;
    $currentDepartment = $user->department ?? '';
@endphp

<div class="row g-3">
    <div class="col-12">
        <label class="form-label d-block text-center">Profile Picture</label>
        @include('partials.photo-picker', ['name' => 'photo', 'current' => $user?->photoUrl(), 'round' => true, 'maxMb' => 2])
        <div class="text-muted small mt-1 text-center">JPG or PNG, max 2MB.</div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Full Name <span class="required">*</span></label>
        <input type="text" name="name" value="{{ $user->name ?? '' }}" class="form-control" placeholder="Enter full name" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Username <span class="required">*</span></label>
        <input type="text" name="username" value="{{ $user->username ?? '' }}" class="form-control" placeholder="Enter username" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Email <span class="required">*</span></label>
        <input type="email" name="email" value="{{ $user->email ?? '' }}" class="form-control" placeholder="name@mdpt.gov.my" required>
    </div>
    <div class="col-md-6">
        @if($user)
            <label class="form-label">New Password</label>
            <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password" autocomplete="new-password">
        @else
            <label class="form-label">Password <span class="required">*</span></label>
            <input type="password" name="password" class="form-control" placeholder="At least 8 characters" autocomplete="new-password" minlength="8" required>
        @endif
    </div>
    <div class="col-md-6">
        <label class="form-label">Role <span class="required">*</span></label>
        <select name="role" class="form-select" required>
            @unless($user)
                <option value="" selected disabled>Select role</option>
            @endunless
            @foreach($roles as $value => $label)
                <option value="{{ $value }}" @selected(($user->role ?? '') === $value)>{{ $label }}</option>
            @endforeach
            @if($user && ! array_key_exists($user->role, $roles))
                <option value="{{ $user->role }}" selected>{{ ucwords(str_replace('_', ' ', $user->role)) }}</option>
            @endif
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Department <span class="required">*</span></label>
        <select name="department" class="form-select" required>
            <option value="" @selected(! $currentDepartment) disabled>Select department</option>
            @foreach($departments as $department)
                <option value="{{ $department }}" @selected($currentDepartment === $department)>{{ $department }}</option>
            @endforeach
            @if($currentDepartment && ! in_array($currentDepartment, $departments, true))
                <option value="{{ $currentDepartment }}" selected>{{ $currentDepartment }}</option>
            @endif
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="active" @selected(($user->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(($user->status ?? '') === 'inactive')>Inactive</option>
        </select>
    </div>
</div>
