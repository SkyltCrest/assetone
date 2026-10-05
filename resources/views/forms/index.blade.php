@extends('layouts.app')

@section('title', 'KEW.PA Forms')
@section('heading', 'KEW.PA Forms')
@section('subheading', 'Print the official Treasury forms, filled in from AssetOne records')

@php
    $meta = $formOptions[$form];
@endphp

@section('content')

<x-banner title="KEW.PA Forms" text="Pick a form, filter the records, then print them ready-filled." :keys="['/' => 'search', 'P' => 'print']">
    <button type="submit" form="formFilters" formaction="{{ route('forms.print') }}" formtarget="_blank" class="btn btn-primary" data-key="p" @disabled($forms->isEmpty())>
        <i class="bi bi-printer me-2"></i>Print {{ $forms->count() }} {{ \Illuminate\Support\Str::plural('form', $forms->count()) }}
    </button>
</x-banner>

{{-- Form picker --}}
<div class="row g-3 mb-4">
    @foreach($formOptions as $value => $option)
        <div class="col-md-6">
            <a href="{{ route('forms.index', ['form' => $value]) }}" class="card content-card p-3 h-100 text-decoration-none {{ $form === $value ? 'border-primary' : '' }}">
                <div class="d-flex align-items-start gap-3">
                    <div class="stat-icon {{ $form === $value ? 'icon-blue' : 'icon-orange' }}"><i class="bi bi-file-earmark-ruled"></i></div>
                    <div>
                        <div class="fw-bold text-body">{{ $option['code'] }} @if($form === $value)<span class="badge bg-primary ms-1">Selected</span>@endif</div>
                        <div class="small text-body">{{ $option['title'] }}</div>
                        <div class="small text-muted">{{ $option['about'] }}</div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="card content-card p-4 mb-4 no-print">
    <form method="GET" action="{{ route('forms.index') }}" id="formFilters">
        <input type="hidden" name="form" value="{{ $form }}">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-funnel me-2"></i>Filters</h5>
            @if(count($filters))
                <a href="{{ route('forms.index', ['form' => $form]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Clear all
                </a>
            @endif
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="{{ $form === 'pa9' ? 'Application no., asset, borrower...' : 'Report ID, asset, description...' }}">
                </div>
            </div>
            @if($canManage)
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted">{{ $form === 'pa9' ? 'Borrower' : 'Reported by' }}</label>
                    <select name="person" class="form-select">
                        <option value="">Anyone</option>
                        @foreach($people as $person)
                            <option value="{{ $person->id }}" @selected(request('person') == $person->id)>{{ $person->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            @if($form === 'pa9' && $canManage)
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted">Department</label>
                    <select name="department" class="form-select">
                        <option value="">All departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department }}" @selected(request('department') === $department)>{{ $department }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Status</label>
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">{{ $form === 'pa9' ? 'Loaned from' : 'Reported from' }}</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">{{ $form === 'pa9' ? 'Loaned until' : 'Reported until' }}</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>
        </div>

        <div class="mt-4 d-flex flex-wrap gap-2 align-items-center">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-2"></i>Apply</button>
            <a href="{{ route('forms.index', ['form' => $form]) }}" class="btn btn-secondary"><i class="bi bi-arrow-clockwise me-2"></i>Reset</a>
        </div>
    </form>
</div>

@if(count($filters))
    <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
        <span class="small text-muted fw-semibold">Applied:</span>
        @foreach($filters as $label => $value)
            <span class="badge bg-info">{{ $label }}: {{ $value }}</span>
        @endforeach
    </div>
@endif

<div class="card content-card p-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
        <h5 class="fw-bold mb-0">{{ $meta['code'] }} forms to print</h5>
        <span class="small text-muted">{{ number_format($forms->count()) }} {{ \Illuminate\Support\Str::plural('form', $forms->count()) }}</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            @if($form === 'pa9')
                <thead>
                    <tr>
                        <th>Application No.</th>
                        <th>Borrower</th>
                        <th>Department</th>
                        <th>Loaned</th>
                        <th>Assets</th>
                        <th>Issued By</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forms as $i => $row)
                        <tr>
                            <td class="fw-semibold">{{ $row['number'] ?: '—' }}</td>
                            <td>{{ $row['applicant'] ?: '—' }}</td>
                            <td>{{ $row['department'] }}</td>
                            <td>{{ $row['borrowed']->format('d M Y') }}</td>
                            <td>
                                @foreach($row['items'] as $item)
                                    <div class="small">
                                        {{ $item->asset->asset_code ?? '—' }} &mdash; {{ $item->asset->name ?? '—' }}
                                        <span class="badge bg-{{ $item->statusColor() }} ms-1">{{ $item->statusLabel() }}</span>
                                    </div>
                                @endforeach
                            </td>
                            <td>{{ $row['issuer'] ?: '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('forms.print', ['form' => 'pa9', 'only' => $row['key']]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-printer me-1"></i>Print
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No loans match the current filters.</td></tr>
                    @endforelse
                </tbody>
            @else
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Report ID</th>
                        <th>Asset</th>
                        <th>Reported By</th>
                        <th>Reported On</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forms as $i => $row)
                        @php $report = $row['report']; @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="fw-semibold">{{ $report->report_code }}</td>
                            <td>{{ $report->asset->asset_code ?? '—' }} &mdash; {{ $report->asset->name ?? '—' }}</td>
                            <td>{{ $report->reporter->name ?? '—' }}</td>
                            <td>{{ $report->created_at->format('d M Y') }}</td>
                            <td><span class="badge bg-{{ $report->statusColor() }}">{{ $report->statusLabel() }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('forms.print', ['form' => 'pa10', 'only' => $row['key']]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-printer me-1"></i>Print
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No reported issues match the current filters.</td></tr>
                    @endforelse
                </tbody>
            @endif
        </table>
    </div>
</div>

@endsection
