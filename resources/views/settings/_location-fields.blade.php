{{-- Location form fields, shared by the Add and Edit pop-ups. $location is null when adding. --}}
@php
    $location = $location ?? null;
    $departments = config('assetone.departments');
    $currentDepartment = $location->department ?? '';
@endphp

<div class="mb-3">
    <label class="form-label">Location Name <span class="required">*</span></label>
    <input type="text" name="name" value="{{ $location->name ?? '' }}" class="form-control" placeholder="e.g. IT Department Office" required>
</div>

<div class="mb-3">
    <label class="form-label">Location Code <span class="required">*</span></label>
        <input type="text" name="code" value="{{ $location->code ?? '' }}" class="form-control text-uppercase font-monospace fw-semibold" placeholder="e.g. IT-L2-01" maxlength="15" pattern="[A-Za-z0-9\-]{1,15}" title="Letters, numbers and dashes only" required>
        <div class="form-text">Short reference code for this location.</div>
    </div>
    <div class="mb-3">
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

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Building / Block</label>
        <input type="text" name="building" value="{{ $location->building ?? '' }}" class="form-control" placeholder="e.g. Block A">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Floor / Level</label>
        <input type="text" name="floor" value="{{ $location->floor ?? '' }}" class="form-control" placeholder="e.g. Level 2">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Room Number</label>
        <input type="text" name="room" value="{{ $location->room ?? '' }}" class="form-control" placeholder="e.g. 2.05">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="2" placeholder="Enter location description">{{ $location->description ?? '' }}</textarea>
</div>

<div class="mb-1">
    <label class="form-label">Status</label>
    <select name="status" class="form-select">
        <option value="active" @selected(($location->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(($location->status ?? '') === 'inactive')>Inactive</option>
    </select>
</div>
