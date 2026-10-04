{{--
    Start date + loan duration + calculated due date.
    $assignment is null when creating; $required marks the duration compulsory.
--}}
@php
    $assignment = $assignment ?? null;
    $required = $required ?? false;
    $start = $assignment ? $assignment->assigned_date->format('Y-m-d') : now()->format('Y-m-d');
    $days = $assignment->loan_days ?? null;
    $isCustom = $days !== null && ! in_array((int) $days, $loanDurations, true);
@endphp

<div class="row g-3 mb-3" data-loan>
    <div class="col-md-6">
        <label class="form-label fw-medium">Start Date <span class="required">*</span></label>
        <input type="date" name="assigned_date" value="{{ $start }}" class="form-control" data-loan-start required>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-medium">Loan Duration @if($required)<span class="required">*</span>@endif</label>
        <select class="form-select" data-loan-preset @if($required) required @endif>
            @unless($required)
                <option value="" @selected($days === null)>No due date</option>
            @endunless
            @foreach($loanDurations as $duration)
                <option value="{{ $duration }}" @selected($assignment ? (int) $days === $duration : $duration === 14)>{{ $duration }} days</option>
            @endforeach
            <option value="custom" @selected($isCustom)>Custom...</option>
        </select>
    </div>
    <div class="col-md-6 {{ $isCustom ? '' : 'd-none' }}" data-loan-custom-wrap>
        <label class="form-label fw-medium">Custom Duration (days) <span class="required">*</span></label>
        <input type="number" class="form-control" min="1" max="365" step="1" value="{{ $isCustom ? $days : '' }}" placeholder="Enter number of days" data-loan-custom>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-medium">Due Date</label>
        <input type="text" class="form-control" value="" placeholder="—" data-loan-due readonly>
    </div>
    <input type="hidden" name="loan_days" value="{{ $days ?? ($assignment ? '' : 14) }}" data-loan-days>
</div>
