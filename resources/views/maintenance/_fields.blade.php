{{-- Maintenance form fields, shared by the Add and Edit pop-ups. $record is null when adding. --}}
@php $record = $record ?? null; @endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Asset <span class="required">*</span></label>
        <select name="asset_id" class="form-select" required>
            @unless($record)
                <option value="" selected disabled>Select asset</option>
            @endunless
            @foreach($assets as $asset)
                <option value="{{ $asset->id }}" @selected($record && $record->asset_id === $asset->id)>{{ $asset->asset_code }} - {{ $asset->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Maintenance Type <span class="required">*</span></label>
        <select name="type" class="form-select" required>
            @unless($record)
                <option value="" selected disabled>Select type</option>
            @endunless
            @foreach($types as $value => $label)
                <option value="{{ $value }}" @selected($record && $record->type === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Maintenance Date <span class="required">*</span></label>
        <input type="date" name="maintenance_date" value="{{ $record ? $record->maintenance_date->format('Y-m-d') : now()->format('Y-m-d') }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Next Maintenance</label>
        <input type="date" name="next_maintenance_date" value="{{ optional($record?->next_maintenance_date)->format('Y-m-d') }}" class="form-control">
        <div class="form-text">Leave empty if no follow-up is planned.</div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Service Provider <span class="required">*</span></label>
        <input type="text" name="service_provider" value="{{ $record->service_provider ?? '' }}" class="form-control" placeholder="e.g. In-house IT Team" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Cost (RM)</label>
        <input type="number" step="0.01" min="0" name="cost" value="{{ $record->cost ?? '' }}" class="form-control" placeholder="0.00">
    </div>
    <div class="col-md-6">
        <label class="form-label">Status <span class="required">*</span></label>
        <select name="status" class="form-select" required>
            @foreach($statuses as $value => $label)
                <option value="{{ $value }}" @selected(($record->status ?? 'pending') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">Notes</label>
        <textarea name="description" class="form-control" rows="3" placeholder="Enter maintenance details...">{{ $record->description ?? '' }}</textarea>
    </div>
</div>
