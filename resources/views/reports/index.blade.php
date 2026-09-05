@extends('layouts.app')

@section('title', 'Asset Report')
@section('heading', 'Asset Report')
@section('subheading', 'Build, filter and print a report of assets managed by MDPT')

@php
    $activeFilters = collect($filters);
@endphp

@section('content')

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Asset Report</h3>
        <p class="text-muted mb-0">Filter the asset register, choose your columns, then print or export.</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <button type="submit" form="reportFilters" formaction="{{ route('reports.export') }}" class="btn btn-outline-primary">
            <i class="bi bi-filetype-csv me-2"></i>Export CSV
        </button>
        <button type="submit" form="reportFilters" formaction="{{ route('reports.print') }}" formtarget="_blank" class="btn btn-primary">
            <i class="bi bi-printer me-2"></i>Print Report
        </button>
    </div>
</div>

{{-- Filters --}}
<div class="card content-card p-4 mb-4 no-print">
    <form method="GET" action="{{ route('reports.index') }}" id="reportFilters">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-funnel me-2"></i>Filters</h5>
            @if($activeFilters->isNotEmpty())
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Clear all
                </a>
            @endif
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Asset code or name...">
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Category</label>
                <select name="category" class="form-select">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Location</label>
                <select name="location" class="form-select">
                    <option value="">All locations</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" @selected(request('location') == $location->id)>{{ $location->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Status</label>
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach($statuses as $assetStatus)
                        <option value="{{ $assetStatus->id }}" @selected(request('status') == $assetStatus->id)>{{ $assetStatus->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Department</label>
                <select name="department" class="form-select">
                    <option value="">All departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department }}" @selected(request('department') === $department)>{{ $department }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Custodian</label>
                <select name="custodian" class="form-select">
                    <option value="">Anyone</option>
                    @foreach($custodians as $custodian)
                        <option value="{{ $custodian->id }}" @selected(request('custodian') == $custodian->id)>{{ $custodian->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Assignment</label>
                <select name="assignment" class="form-select">
                    <option value="">All</option>
                    <option value="assigned" @selected(request('assignment') === 'assigned')>Assigned only</option>
                    <option value="unassigned" @selected(request('assignment') === 'unassigned')>Unassigned only</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Purchased from</label>
                <input type="date" name="purchase_from" value="{{ request('purchase_from') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Purchased until</label>
                <input type="date" name="purchase_to" value="{{ request('purchase_to') }}" class="form-control">
            </div>
        </div>

        <hr class="my-4">

        <div class="row g-3">
            <div class="col-lg-8">
                <label class="form-label small fw-semibold text-muted">Columns to include</label>
                <div class="d-flex flex-wrap gap-3">
                    @foreach($allColumns as $key => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="columns[]" value="{{ $key }}"
                                   id="col-{{ $key }}" @checked(in_array($key, $selectedColumns, true))>
                            <label class="form-check-label small" for="col-{{ $key }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-4">
                <label class="form-label small fw-semibold text-muted">Sort by</label>
                <div class="d-flex gap-2">
                    <select name="sort" class="form-select">
                        <option value="asset_code" @selected($sort === 'asset_code')>Asset ID</option>
                        <option value="name" @selected($sort === 'name')>Asset Name</option>
                        <option value="purchase_date" @selected($sort === 'purchase_date')>Purchase Date</option>
                        <option value="purchase_price" @selected($sort === 'purchase_price')>Purchase Price</option>
                    </select>
                    <select name="dir" class="form-select">
                        <option value="asc" @selected($dir === 'asc')>Asc</option>
                        <option value="desc" @selected($dir === 'desc')>Desc</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-2"></i>Apply</button>
        </div>
    </form>
</div>

{{-- Active filter summary (also visible on the on-screen report) --}}
@if($activeFilters->isNotEmpty())
    <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
        <span class="small text-muted fw-semibold">Applied:</span>
        @foreach($activeFilters as $label => $value)
            <span class="badge bg-info">{{ $label }}: {{ $value }}</span>
        @endforeach
    </div>
@endif

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted small text-uppercase fw-medium mb-1">Assets in Report</p>
                <h3 class="fw-bold mb-0">{{ number_format($reportCount) }}</h3>
            </div>
            <div class="stat-icon icon-blue"><i class="bi bi-box-seam"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted small text-uppercase fw-medium mb-1">Assigned</p>
                <h3 class="fw-bold mb-0">{{ number_format($assignedCount) }}</h3>
            </div>
            <div class="stat-icon icon-green"><i class="bi bi-person-check"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted small text-uppercase fw-medium mb-1">Unassigned</p>
                <h3 class="fw-bold mb-0">{{ number_format($unassignedCount) }}</h3>
            </div>
            <div class="stat-icon icon-orange"><i class="bi bi-person-dash"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted small text-uppercase fw-medium mb-1">Total Value (RM)</p>
                <h3 class="fw-bold mb-0">{{ number_format($totalValue, 2) }}</h3>
            </div>
            <div class="stat-icon icon-blue"><i class="bi bi-cash-stack"></i></div>
        </div>
    </div>
</div>

<div class="card content-card p-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3">
        <h5 class="fw-bold mb-0">Asset Summary</h5>
        <span class="small text-muted">{{ number_format($reportCount) }} {{ \Illuminate\Support\Str::plural('asset', $reportCount) }}</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>No.</th>
                    @foreach($selectedColumns as $key)
                        @php
                            $isSortable = in_array($key, $sortable, true);
                            $numeric = $key === 'purchase_price';
                            $nextDir = ($sort === $key && $dir === 'asc') ? 'desc' : 'asc';
                        @endphp
                        <th @class(['text-end' => $numeric])>
                            @if($isSortable)
                                <a href="{{ route('reports.index', array_merge(request()->query(), ['sort' => $key, 'dir' => $nextDir])) }}"
                                   class="text-reset text-decoration-none">
                                    {{ $allColumns[$key] }}
                                    @if($sort === $key)
                                        <i class="bi bi-caret-{{ $dir === 'asc' ? 'up' : 'down' }}-fill small"></i>
                                    @endif
                                </a>
                            @else
                                {{ $allColumns[$key] }}
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $i => $asset)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        @foreach($selectedColumns as $key)
                            <td @class(['text-end' => $key === 'purchase_price'])>
                                @include('reports.partials.cell', ['asset' => $asset, 'column' => $key])
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($selectedColumns) + 1 }}" class="text-center text-muted py-4">
                            No assets match the current filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($reportCount > 0 && in_array('purchase_price', $selectedColumns, true))
                <tfoot>
                    <tr>
                        <th colspan="{{ array_search('purchase_price', $selectedColumns, true) + 1 }}" class="text-end">Total</th>
                        <th class="text-end">{{ number_format($totalValue, 2) }}</th>
                        @if(array_search('purchase_price', $selectedColumns, true) < count($selectedColumns) - 1)
                            <th colspan="{{ count($selectedColumns) - 1 - array_search('purchase_price', $selectedColumns, true) }}"></th>
                        @endif
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection
