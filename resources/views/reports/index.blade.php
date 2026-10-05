@extends('layouts.app')

@section('title', 'Asset Report')
@section('heading', 'Asset Report')
@section('subheading', 'Build, filter and print a report of assets managed by MDPT')

@php
    $activeFilters = collect($filters);
@endphp

@section('content')

<x-banner title="Asset Report" text="Filter the asset register, choose your columns, then print or export." :keys="['/' => 'search', 'P' => 'print']">
    <button type="submit" form="reportFilters" formaction="{{ route('reports.export') }}" class="btn btn-secondary">
        <i class="bi bi-download me-2"></i>Export CSV
    </button>
    <button type="submit" form="reportFilters" formaction="{{ route('reports.print') }}" formtarget="_blank" class="btn btn-primary" data-key="p">
        <i class="bi bi-printer me-2"></i>Print Report
    </button>
</x-banner>

{{-- Filters --}}
<div class="card content-card p-4 mb-4 no-print">
    <form method="GET" action="{{ route('reports.index') }}" id="reportFilters">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-funnel me-2"></i>Filters</h5>
            <span data-live>
            @if($activeFilters->isNotEmpty())
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Clear all
                </a>
            @endif
            </span>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Code, name, custodian, supplier...">
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
                <div class="col-checks">
                    @foreach($allColumns as $key => $label)
                        <label class="ccheck">
                            <input type="checkbox" name="columns[]" value="{{ $key }}" @checked(in_array($key, $selectedColumns, true))>
                            <span>{{ $label }}</span>
                        </label>
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
                <label class="form-label small fw-semibold text-muted mt-3">Group by</label>
                <select name="group" class="form-select">
                    <option value="">No grouping</option>
                    @foreach($groupOptions as $value => $label)
                        <option value="{{ $value }}" @selected($group === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4 d-flex flex-wrap gap-2 align-items-center">
            <button type="submit" class="btn btn-primary btn-apply" id="btnApply"><i class="bi bi-check2 me-2"></i>Apply</button>
            <a href="{{ route('reports.index', ['reset' => 1]) }}" class="btn btn-secondary"><i class="bi bi-arrow-clockwise me-2"></i>Reset</a>
            <div class="ms-auto d-flex gap-2 flex-wrap">
                <input type="text" id="saveName" class="form-control" style="width:220px" placeholder="Name this report..." maxlength="40" form="noForm">
                <button type="button" class="btn btn-outline-primary" id="btnSave"><i class="bi bi-bookmark-plus me-2"></i>Save report</button>
            </div>
        </div>
        <div class="saved" id="savedList"></div>
    </form>
</div>

{{-- Active filter summary (also visible on the on-screen report) --}}
<div data-live>
@if($activeFilters->isNotEmpty())
    <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
        <span class="small text-muted fw-semibold">Applied:</span>
        @foreach($activeFilters as $label => $value)
            <span class="badge bg-info">{{ $label }}: {{ $value }}</span>
        @endforeach
    </div>
@endif
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted small text-uppercase fw-medium mb-1">Assets in Report</p>
                <h3 class="fw-bold mb-0" data-live>{{ number_format($reportCount) }}</h3>
            </div>
            <div class="stat-icon icon-blue"><i class="bi bi-box-seam"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted small text-uppercase fw-medium mb-1">Assigned</p>
                <h3 class="fw-bold mb-0" data-live>{{ number_format($assignedCount) }}</h3>
            </div>
            <div class="stat-icon icon-green"><i class="bi bi-person-check"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted small text-uppercase fw-medium mb-1">Unassigned</p>
                <h3 class="fw-bold mb-0" data-live>{{ number_format($unassignedCount) }}</h3>
            </div>
            <div class="stat-icon icon-orange"><i class="bi bi-person-dash"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card d-flex justify-content-between align-items-center">
            <div>
                <p class="text-muted small text-uppercase fw-medium mb-1">Total Value (RM)</p>
                <h3 class="fw-bold mb-0" data-live>{{ number_format($totalValue, 2) }}</h3>
            </div>
            <div class="stat-icon icon-blue"><i class="bi bi-cash-stack"></i></div>
        </div>
    </div>
</div>

<div class="card content-card p-4" data-live>
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
                    @if($group && ($i === 0 || $groupLabels[$asset->id] !== $groupLabels[$assets[$i - 1]->id]))
                        @php $summary = $groupSummary[$groupLabels[$asset->id]]; @endphp
                        <tr class="group-row">
                            <td colspan="{{ count($selectedColumns) + 1 }}">
                                <i class="bi bi-folder2-open me-2"></i>{{ $groupOptions[$group] }}: <strong>{{ $groupLabels[$asset->id] }}</strong>
                                <span class="text-muted ms-2">{{ $summary['count'] }} {{ \Illuminate\Support\Str::plural('asset', $summary['count']) }} &middot; RM {{ number_format($summary['value'], 2) }}</span>
                            </td>
                        </tr>
                    @endif
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

@push('scripts')
<script>
// Saved reports (kept in this browser) and the "filters changed" pulse on Apply.
(function () {
    var KEY = 'assetone_rep_saved', list = document.getElementById('savedList'), form = document.getElementById('reportFilters'),
        apply = document.getElementById('btnApply'), name = document.getElementById('saveName');
    var read = function () { try { return JSON.parse(localStorage.getItem(KEY) || '[]') || []; } catch (e) { return []; } };
    var write = function (v) { try { localStorage.setItem(KEY, JSON.stringify(v)); } catch (e) {} };

    function render() {
        var items = read();
        list.innerHTML = '';
        if (!items.length) return;
        var lbl = document.createElement('span'); lbl.className = 'lbl'; lbl.innerHTML = '<i class="bi bi-bookmarks me-1"></i>Saved reports';
        list.appendChild(lbl);
        items.forEach(function (it, i) {
            var chip = document.createElement('span'); chip.className = 'sq'; chip.setAttribute('role', 'button');
            chip.appendChild(document.createTextNode(it.name));
            var del = document.createElement('button'); del.type = 'button'; del.setAttribute('aria-label', 'Delete'); del.innerHTML = '<i class="bi bi-x"></i>';
            del.addEventListener('click', function (e) { e.stopPropagation(); var l = read(); l.splice(i, 1); write(l); render(); });
            chip.appendChild(del);
            chip.addEventListener('click', function () { window.aoLive.go(@json(route('reports.index')) + '?' + it.q); });
            list.appendChild(chip);
        });
    }

    document.getElementById('btnSave').addEventListener('click', function () {
        var n = name.value.trim();
        if (!n) { name.focus(); if (window.aoToast) window.aoToast('Give the report a name first.'); return; }
        var q = new URLSearchParams(new FormData(form)).toString();
        var l = read().filter(function (s) { return s.name.toLowerCase() !== n.toLowerCase(); });
        l.push({ name: n, q: q }); write(l); name.value = ''; render();
        if (window.aoToast) window.aoToast('Saved "' + n + '"');
    });
    name.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btnSave').click(); } });

    ['input', 'change'].forEach(function (ev) {
        form.addEventListener(ev, function (e) { if (e.target !== name) apply.classList.add('dirty'); });
    });
    form.addEventListener('submit', function (e) {
        if (!form.querySelector('input[name="columns[]"]:checked')) {
            e.preventDefault();
            if (window.aoToast) window.aoToast('Choose at least one column for the report.');
        }
    });
    document.addEventListener('ao:live', function () { apply.classList.remove('dirty'); });
    render();
})();
</script>
@endpush
