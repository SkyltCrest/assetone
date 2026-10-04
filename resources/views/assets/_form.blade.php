{{--
    Shared asset form: the five sections on the left and the progress panel on the right.
    $submitLabel is the text on the save button.
--}}
@php
    $selectedCategory = (int) old('asset_category_id', $asset->asset_category_id);
    $selectedType = (int) old('asset_type_id', $asset->asset_type_id);
    $selectedLocation = (int) old('asset_location_id', $asset->asset_location_id);
    $currentDepartment = old('department', $asset->department);
    $activeStatusId = optional($statuses->first(fn ($s) => strcasecmp($s->name, \App\Models\Asset::STATUS_ACTIVE) === 0))->id;
    $selectedStatus = (int) old('asset_status_id', $asset->asset_status_id ?: $activeStatusId);

    // Types per category, for the dependent "Asset Type" dropdown.
    $typesByCategory = $categories->mapWithKeys(fn ($c) => [$c->id => $c->types->map->only(['id', 'name', 'code'])->values()]);
    // Locations per department, for the dependent "Asset Location" dropdown.
    // Shown when a department has no location of its own, so registration is never blocked.
    $allLocations = $locations->map(fn ($l) => ['id' => $l->id, 'label' => $l->name.($l->code ? ' ('.$l->code.')' : '').($l->department ? ' — '.$l->department : '')])->values();
    $locationsByDepartment = $locations->groupBy('department')->map(fn ($group) => $group->map(fn ($l) => ['id' => $l->id, 'label' => $l->name.($l->code ? ' ('.$l->code.')' : '')])->values());
@endphp

<div class="row g-4">
    <div class="col-xl-8 col-12">

        <div class="card form-card p-4 mb-4" data-form-card>
            <h5 class="section-title mb-4"><i class="bi bi-box-seam me-2"></i>Basic Asset Information</h5>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label" for="assetName">Asset Name <span class="required">*</span></label>
                    <input type="text" id="assetName" name="name" value="{{ old('name', $asset->name) }}" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Dell Latitude 5420" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="assetCategory">Asset Category <span class="required">*</span></label>
                    <select id="assetCategory" name="asset_category_id" class="form-select @error('asset_category_id') is-invalid @enderror" required>
                        <option value="" @selected(! $selectedCategory) disabled>Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected($selectedCategory === $category->id)>{{ $category->name }}{{ $category->short_code ? ' ('.$category->short_code.')' : '' }}</option>
                        @endforeach
                    </select>
                    @error('asset_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="assetType">Asset Type <span class="required">*</span></label>
                    <select id="assetType" name="asset_type_id" class="form-select @error('asset_type_id') is-invalid @enderror" data-selected="{{ $selectedType ?: '' }}" required>
                        <option value="">Select category first</option>
                    </select>
                    @error('asset_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="assetCode">Asset Code <span class="required">*</span></label>
                    <input type="text" id="assetCode" class="form-control" value="{{ $asset->asset_code }}" placeholder="Auto generated" data-current="{{ $asset->asset_code }}" readonly>
                    <div class="form-text">Auto-generated from Category Code, Type Code, Year &amp; Running No.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="serialNumber">Serial Number <span class="required">*</span></label>
                    <input type="text" id="serialNumber" name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" class="form-control @error('serial_number') is-invalid @enderror" placeholder="Enter serial number" required>
                    @error('serial_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="assetStatus">Asset Status <span class="required">*</span></label>
                    <select id="assetStatus" name="asset_status_id" class="form-select @error('asset_status_id') is-invalid @enderror" required>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" @selected($selectedStatus === $status->id)>{{ $status->name }}</option>
                        @endforeach
                    </select>
                    @error('asset_status_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label" for="assetDescription">Description</label>
                    <textarea id="assetDescription" name="description" class="form-control @error('description') is-invalid @enderror" rows="4" maxlength="500" placeholder="Enter additional information about the asset">{{ old('description', $asset->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text text-end" id="descCount">0 / 500</div>
                </div>
            </div>
        </div>

        <div class="card form-card p-4 mb-4" data-form-card>
            <h5 class="section-title mb-4"><i class="bi bi-image me-2"></i>Asset Photo <span class="required ms-1">*</span></h5>
            <div id="assetPhotoBox">
                @include('partials.photo-picker', [
                    'name' => 'photo',
                    'current' => $asset->photoUrl(),
                    'required' => ! $asset->photo,
                    'crop' => '4:3',
                    'maxMb' => 5,
                ])
            </div>
            <div class="form-text mt-3">
                <i class="bi bi-info-circle me-1"></i><strong>Camera</strong> opens the live camera (or the phone camera). <strong>Upload</strong> picks a file, or drop a picture onto the preview.<br>
                Supported: JPG, PNG, WEBP (max 5MB). Pictures are cropped to a 4:3 frame automatically.
                {{ $asset->photo ? 'Leave as is to keep the current photo.' : '' }}
            </div>
        </div>

        <div class="card form-card p-4 mb-4" data-form-card>
            <h5 class="section-title mb-4"><i class="bi bi-cart-check me-2"></i>Purchase Information</h5>
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label" for="purchaseDate">Purchase Date <span class="required">*</span></label>
                    <input type="date" id="purchaseDate" name="purchase_date" value="{{ old('purchase_date', optional($asset->purchase_date)->format('Y-m-d')) }}" class="form-control @error('purchase_date') is-invalid @enderror" max="{{ today()->format('Y-m-d') }}" required>
                    @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="purchasePrice">Purchase Price (RM) <span class="required">*</span></label>
                    <input type="number" id="purchasePrice" name="purchase_price" value="{{ old('purchase_price', $asset->purchase_price) }}" class="form-control @error('purchase_price') is-invalid @enderror" placeholder="0.00" step="0.01" min="0" required>
                    @error('purchase_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="supplier">Supplier</label>
                    <input type="text" id="supplier" name="supplier" value="{{ old('supplier', $asset->supplier) }}" class="form-control" placeholder="Enter supplier name">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="poReference">Purchase Order / Reference No.</label>
                    <input type="text" id="poReference" name="po_reference" value="{{ old('po_reference', $asset->po_reference) }}" class="form-control" placeholder="e.g. PO-2026-001">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="warrantyDate">Warranty Expiry Date</label>
                    <input type="date" id="warrantyDate" name="warranty_expiry_date" value="{{ old('warranty_expiry_date', optional($asset->warranty_expiry_date)->format('Y-m-d')) }}" class="form-control @error('warranty_expiry_date') is-invalid @enderror">
                    @error('warranty_expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div id="warrantyHint"></div>
                </div>
            </div>
        </div>

        <div class="card form-card p-4 mb-4" data-form-card>
            <h5 class="section-title mb-4"><i class="bi bi-geo-alt me-2"></i>Asset Location</h5>
            <div class="info-box location-info">
                <i class="bi bi-info-circle-fill"></i>
                <div><strong>Location is where the asset physically sits.</strong><span>For example "IT Staff Room", "Finance Office" or "Server Room". It can change when the asset is moved.</span></div>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label" for="department">Department <span class="required">*</span></label>
                    <select id="department" name="department" class="form-select @error('department') is-invalid @enderror" required>
                        <option value="" @selected(! $currentDepartment) disabled>Select department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department }}" @selected($currentDepartment === $department)>{{ $department }}</option>
                        @endforeach
                        @if($currentDepartment && ! in_array($currentDepartment, $departments, true))
                            <option value="{{ $currentDepartment }}" selected>{{ $currentDepartment }}</option>
                        @endif
                    </select>
                    <div class="form-text">The department responsible for this location.</div>
                    @error('department')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="assetLocation">Asset Location @unless($asset->exists)<span class="required">*</span>@endunless</label>
                    <select id="assetLocation" name="asset_location_id" class="form-select @error('asset_location_id') is-invalid @enderror" data-selected="{{ $selectedLocation ?: '' }}" @unless($asset->exists) required @endunless>
                        <option value="">Select department first</option>
                    </select>
                    <div class="form-text">Physical location of the asset. Locations are managed in <a href="{{ route('asset-management.index', ['tab' => 'location']) }}" target="_blank">Asset Management</a>.</div>
                    @error('asset_location_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="card form-card p-4 mb-4" data-form-card>
            <h5 class="section-title mb-4"><i class="bi bi-person-check me-2"></i>Asset Custodian</h5>
            <div class="info-box custodian-info">
                <i class="bi bi-info-circle-fill"></i>
                <div><strong>The custodian is the person responsible for the asset.</strong><span>The PIC (Person In Charge) is chosen from User Management and can be changed when staff move.</span></div>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label" for="custodian">Person in Charge (PIC) @unless($asset->exists)<span class="required">*</span>@endunless</label>
                    <select id="custodian" name="custodian_id" class="form-select @error('custodian_id') is-invalid @enderror" @unless($asset->exists) required @endunless>
                        <option value="" @selected(! old('custodian_id', $asset->custodian_id)) @unless($asset->exists) disabled @endunless>{{ $asset->exists ? 'No custodian' : 'Select PIC' }}</option>
                        @foreach($custodians as $custodian)
                            <option value="{{ $custodian->id }}" data-name="{{ $custodian->name }}" @selected((int) old('custodian_id', $asset->custodian_id) === $custodian->id)>{{ $custodian->name }} - {{ ucwords(str_replace('_', ' ', $custodian->role)) }}{{ $custodian->department ? ' ('.str_replace(' Department', '', $custodian->department).')' : '' }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        The PIC list comes from User Management.
                        @if(auth()->user()->canManageUsers())<a href="{{ route('users.index') }}" target="_blank">Manage users</a>@endif
                    </div>
                    @error('custodian_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="assignedDate">Assigned Date</label>
                    <input type="date" id="assignedDate" name="assigned_date" value="{{ old('assigned_date', optional($asset->assigned_date)->format('Y-m-d')) }}" class="form-control @error('assigned_date') is-invalid @enderror">
                    <div class="form-text">Date when the asset was assigned to the PIC.</div>
                    @error('assigned_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Progress, live summary and the single save button --}}
    <aside class="col-xl-4">
        <div class="reg-side g content-card animate-in delay-3" id="regSide">
            <div class="ring">
                <svg viewBox="0 0 120 120" width="132" height="132">
                    <defs><linearGradient id="ringGrad" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#42a5f5"/><stop offset="1" stop-color="#10b981"/></linearGradient></defs>
                    <circle class="bg" cx="60" cy="60" r="52"/><circle class="fg" id="ringFg" cx="60" cy="60" r="52"/>
                </svg>
                <div class="ring-num"><span id="ringPct">0%</span><small>COMPLETE</small></div>
            </div>
            <div class="steps" id="steps">
                <div class="rstep" data-i="0"><i class="bi bi-box-seam"></i>Basic Information</div>
                <div class="rstep" data-i="1"><i class="bi bi-image"></i>Asset Photo</div>
                <div class="rstep" data-i="2"><i class="bi bi-cart-check"></i>Purchase Details</div>
                <div class="rstep" data-i="3"><i class="bi bi-geo-alt"></i>Location</div>
                <div class="rstep" data-i="4"><i class="bi bi-person-check"></i>Custodian (PIC)</div>
            </div>
            <img class="sum-thumb" id="sumThumb" alt="Asset preview" @if($asset->photoUrl()) src="{{ $asset->photoUrl() }}" style="display:block" @endif>
            <div class="sum">
                <div class="sum-row"><span>Name</span><b id="sName">-</b></div>
                <div class="sum-row"><span>Category</span><b id="sCat">-</b></div>
                <div class="sum-row"><span>Type</span><b id="sType">-</b></div>
                <div class="sum-row"><span>Code</span><b id="sCode">-</b></div>
                <div class="sum-row"><span>Serial</span><b id="sSerial">-</b></div>
                <div class="sum-row"><span>Price</span><b id="sPrice">-</b></div>
                <div class="sum-row"><span>Status</span><b id="sStatus">-</b></div>
                <div class="sum-row"><span>Location</span><b id="sLoc">-</b></div>
                <div class="sum-row"><span>PIC</span><b id="sPic">-</b></div>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2" id="sideSubmit" disabled><i class="bi bi-check2-circle me-2"></i>{{ $submitLabel }}</button>
            <div class="text-center mt-2 form-text" id="remainHint"></div>
            <button type="button" class="btn btn-secondary w-100 mt-2" id="sideReset"><i class="bi bi-arrow-clockwise me-2"></i>Reset</button>
            <a href="{{ $asset->exists ? route('assets.show', $asset) : route('assets.index') }}" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
            <div class="text-center mt-2 form-text"><kbd>Ctrl</kbd> + <kbd>Enter</kbd> to submit</div>
        </div>
    </aside>
</div>

@push('scripts')
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
    var typesByCategory = @json($typesByCategory);
    var locationsByDepartment = @json($locationsByDepartment);
    var allLocations = @json($allLocations);

    var form = $('assetName').form, category = $('assetCategory'), type = $('assetType'), code = $('assetCode'),
        purchase = $('purchaseDate'), department = $('department'), location = $('assetLocation'),
        custodian = $('custodian'), assigned = $('assignedDate');
    var current = code.dataset.current;
    var previewUrl = @json(route('assets.next-code'));
    var hasPhoto = @json((bool) $asset->photo), existingPhoto = hasPhoto;

    /* ---------- Category -> Type ---------- */
    function fillTypes() {
        var list = typesByCategory[category.value] || [], wanted = type.dataset.selected;
        type.innerHTML = '';
        type.add(new Option(category.value ? (list.length ? 'Select asset type' : 'No types for this category yet') : 'Select category first', ''));
        list.forEach(function (t) { type.add(new Option(t.name + ' (' + t.code + ')', t.id, false, String(t.id) === String(wanted))); });
        type.disabled = !list.length;
    }

    /* ---------- Department -> Location ---------- */
    function fillLocations() {
        var own = locationsByDepartment[department.value] || [], wanted = location.dataset.selected;
        // A department without its own locations may use any registered location.
        var list = !department.value ? [] : own.length ? own : allLocations;
        location.innerHTML = '';
        location.add(new Option(department.value ? (list.length ? (own.length ? 'Select asset location' : 'Select asset location (all locations)') : 'No locations registered yet') : 'Select department first', ''));
        list.forEach(function (l) { location.add(new Option(l.label, l.id, false, String(l.id) === String(wanted))); });
        location.disabled = !list.length;
    }

    /* ---------- Asset code preview (the final number is assigned on save) ---------- */
    var isGenerated = /^[A-Z0-9]+-[A-Z0-9]+-\d{4}-\d{3,}$/;
    function previewCode() {
        if (current && !isGenerated.test(current)) { code.value = current; update(); return; }   // older codes are kept
        if (!type.value || !purchase.value) { code.value = current || ''; update(); return; }
        fetch(previewUrl + '?type=' + encodeURIComponent(type.value) + '&purchase_date=' + encodeURIComponent(purchase.value), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data) return;
                var prefix = data.code.replace(/\d+$/, '');
                code.value = current && current.indexOf(prefix) === 0 ? current : data.code;
                update();
            })
            .catch(function () {});
    }

    category.addEventListener('change', function () { type.dataset.selected = ''; fillTypes(); previewCode(); });
    type.addEventListener('change', function () { type.dataset.selected = type.value; previewCode(); });
    purchase.addEventListener('change', previewCode);
    department.addEventListener('change', function () { location.dataset.selected = ''; fillLocations(); });
    location.addEventListener('change', function () { location.dataset.selected = location.value; });

    // Choosing a custodian defaults the assigned date to today.
    custodian.addEventListener('change', function () {
        if (custodian.value && !assigned.value) assigned.value = new Date().toISOString().slice(0, 10);
    });

    /* ---------- Progress ring, steps, live summary, save button ---------- */
    var cards = [].slice.call(document.querySelectorAll('[data-form-card]'));
    var required = [].slice.call(form.querySelectorAll('[data-form-card] [required]')).filter(function (el) { return el.type !== 'file'; });
    var text = function (sel) { var o = sel.selectedOptions[0]; return o && o.value ? o.textContent.trim() : ''; };
    var set = function (id, v) { $(id).textContent = v || '-'; };

    function update() {
        var missing = required.filter(function (el) { return !String(el.value || '').trim(); }).length + (hasPhoto ? 0 : 1);
        var total = required.length + 1, pct = Math.round((total - missing) / total * 100);
        $('ringFg').style.strokeDashoffset = 326.7 * (1 - pct / 100);
        $('ringPct').textContent = pct + '%';

        cards.forEach(function (card, i) {
            var req = [].slice.call(card.querySelectorAll('[required]')).filter(function (el) { return el.type !== 'file'; });
            var done = req.every(function (el) { return String(el.value || '').trim(); }) && (i !== 1 || hasPhoto);
            var step = document.querySelector('.rstep[data-i="' + i + '"]');
            if (step) step.classList.toggle('done', done);
        });

        set('sName', $('assetName').value.trim());
        set('sCat', text(category)); set('sType', text(type)); set('sCode', code.value);
        set('sSerial', $('serialNumber').value.trim());
        set('sPrice', $('purchasePrice').value ? 'RM ' + Number($('purchasePrice').value).toFixed(2) : '');
        set('sStatus', text($('assetStatus'))); set('sLoc', text(location));
        var pic = custodian.selectedOptions[0]; set('sPic', pic && pic.value ? pic.dataset.name : '');

        var btn = $('sideSubmit'), hint = $('remainHint');
        btn.disabled = missing > 0;
        hint.textContent = missing ? missing + ' required item' + (missing === 1 ? '' : 's') + ' left' : 'All set. Ready to save.';
        hint.classList.toggle('ok', !missing);

        // Warranty hint
        var w = $('warrantyDate').value, html = '';
        if (w) {
            var days = Math.ceil((new Date(w) - new Date()) / 864e5), c = days < 0 ? 'bad' : days <= 90 ? 'warn' : 'ok';
            html = '<span class="hint ' + c + '"><i class="bi bi-shield-' + (days < 0 ? 'x' : 'check') + '"></i>' + (days < 0 ? 'Expired ' + (-days) + ' days ago' : days + ' days of warranty left') + '</span>';
        }
        if ($('warrantyHint').innerHTML !== html) $('warrantyHint').innerHTML = html;
        $('descCount').textContent = $('assetDescription').value.length + ' / 500';
    }

    form.addEventListener('input', update);
    form.addEventListener('change', update);
    $('assetPhotoBox').addEventListener('photo:change', function (e) {
        hasPhoto = e.detail.count > 0 || existingPhoto;
        var th = $('sumThumb');
        if (e.detail.url) { th.src = e.detail.url; th.style.display = 'block'; }
        else if (!existingPhoto) th.style.display = 'none';
        update();
    });

    /* ---------- Steps scroll to their section and follow the page ---------- */
    document.querySelectorAll('.rstep').forEach(function (s) {
        s.addEventListener('click', function () { cards[+s.dataset.i].scrollIntoView({ behavior: reduced ? 'auto' : 'smooth', block: 'center' }); });
    });
    if ('IntersectionObserver' in window) {
        var spy = new IntersectionObserver(function (es) {
            es.forEach(function (e) {
                if (!e.isIntersecting) return;
                document.querySelectorAll('.rstep').forEach(function (s, i) { s.classList.toggle('active', cards[i] === e.target); });
            });
        }, { rootMargin: '-35% 0px -55% 0px' });
        cards.forEach(function (c) { spy.observe(c); });
    }

    /* ---------- Ctrl+Enter saves, or jumps to the first thing still missing ---------- */
    function trySubmit() {
        var first = required.filter(function (el) { return !String(el.value || '').trim(); })[0] || (hasPhoto ? null : $('assetPhotoBox'));
        if (first) {
            var side = $('regSide'); side.classList.remove('gx-shake'); void side.offsetWidth; side.classList.add('gx-shake');
            first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(function () { if (first.focus) first.focus({ preventScroll: true }); }, 350);
            return;
        }
        form.requestSubmit ? form.requestSubmit() : form.submit();
    }
    document.addEventListener('keydown', function (e) { if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') { e.preventDefault(); trySubmit(); } });

    /* ---------- Reset puts back the original values ---------- */
    $('sideReset').addEventListener('click', function () {
        form.reset();
        type.dataset.selected = @json($selectedType ?: '');
        location.dataset.selected = @json($selectedLocation ?: '');
        fillTypes(); fillLocations(); previewCode();
        hasPhoto = existingPhoto;
        update();
        if (window.aoToast) window.aoToast(@json($asset->exists ? 'Changes discarded.' : 'Form has been reset.'));
    });

    fillTypes();
    fillLocations();
    previewCode();
    update();
})();
</script>
@endpush
