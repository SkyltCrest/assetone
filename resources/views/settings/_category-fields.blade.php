{{-- Category form fields, shared by the Add and Edit pop-ups. $category is null when adding. --}}
@php
    $category = $category ?? null;
    $types = $category ? $category->types : collect();
@endphp

<div class="code-help mb-3">
    <i class="bi bi-lightbulb-fill me-2"></i>
    The <strong>Category Code</strong> and <strong>Type Code</strong> are used to build each <strong>Asset Code</strong> automatically
    (for example <code>C-LAP-2026-001</code>).
</div>

<div class="row">
    <div class="col-md-8 mb-3">
        <label class="form-label">Category Name <span class="required">*</span></label>
        <input type="text" name="name" value="{{ $category->name ?? '' }}" class="form-control" placeholder="e.g. Computer &amp; IT Equipment" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Category Code <span class="required">*</span></label>
        <input type="text" name="short_code" value="{{ $category->short_code ?? '' }}" class="form-control text-uppercase font-monospace fw-semibold" placeholder="e.g. C" maxlength="5" pattern="[A-Za-z]{1,5}" title="1 to 5 letters" required>
        <div class="form-text">Short code (e.g. C, OE, F, V)</div>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="2" placeholder="Enter category description">{{ $category->description ?? '' }}</textarea>
</div>

<div class="mb-3" data-types-editor>
    <label class="form-label">Asset Types <span class="required">*</span></label>
    <div class="types-list" data-types-list>
        @foreach($types as $i => $type)
            <div class="row g-2 mb-2 align-items-center" data-type-row>
                <input type="hidden" name="types[{{ $i }}][id]" value="{{ $type->id }}">
                <div class="col-7"><input type="text" name="types[{{ $i }}][name]" value="{{ $type->name }}" class="form-control" placeholder="Type name (e.g. Laptop)" required></div>
                <div class="col-3"><input type="text" name="types[{{ $i }}][code]" value="{{ $type->code }}" class="form-control text-uppercase font-monospace fw-semibold" placeholder="Code" maxlength="5" pattern="[A-Za-z]{1,5}" title="1 to 5 letters" required></div>
                <div class="col-2"><button type="button" class="btn btn-outline-danger w-100" data-type-remove title="Remove type" @if(($type->assets_count ?? 0) > 0) disabled @endif><i class="bi bi-x-lg"></i></button></div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-outline-primary btn-sm" data-type-add><i class="bi bi-plus-lg me-1"></i>Add Type</button>
    <div class="form-text">Each type needs a <strong>name</strong> and a <strong>short code</strong>. A type that already has assets cannot be removed.</div>
</div>

<div class="mb-1">
    <label class="form-label">Status</label>
    <select name="status" class="form-select">
        <option value="active" @selected(($category->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(($category->status ?? '') === 'inactive')>Inactive</option>
    </select>
</div>
