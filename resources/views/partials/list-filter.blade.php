{{--
    Search box + status filter above a list.
      $action        route the form submits to
      $placeholder   search box hint
      $options       ['value' => 'Label'] for the status dropdown
--}}
<form method="GET" action="{{ $action }}" class="row g-3 mb-3 filter-row">
    <div class="col-12 col-md-8">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ $placeholder }}">
        </div>
    </div>
    <div class="col-12 col-md-4">
        <select name="status" class="form-select" aria-label="Filter by status" onchange="this.form.requestSubmit()">
            <option value="">All Status</option>
            @foreach($options as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</form>
