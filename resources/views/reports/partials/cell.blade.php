@php
    /** @var \App\Models\Asset $asset */
    $plain = $plain ?? false;
@endphp
@switch($column)
    @case('photo')
        @if($plain)
            @if($asset->photoUrl())<img src="{{ $asset->photoUrl() }}" alt="" style="width:48px;height:36px;object-fit:cover">@endif
        @else
            @include('partials.thumb', ['url' => $asset->photoUrl(), 'alt' => $asset->name, 'info' => $asset->asset_code.' · '.($asset->assetStatus->name ?? '-').($asset->custodian ? ' · '.$asset->custodian->name : '')])
        @endif
        @break
    @case('asset_code')
        <span class="fw-semibold">{{ $asset->asset_code }}</span>
        @break
    @case('name')
        {{ $asset->name }}
        @break
    @case('serial_number')
        {{ $asset->serial_number ?? '—' }}
        @break
    @case('type')
        {{ $asset->type->name ?? '—' }}
        @break
    @case('po_reference')
        {{ $asset->po_reference ?? '—' }}
        @break
    @case('category')
        {{ $asset->category->name ?? '—' }}
        @break
    @case('location')
        {{ $asset->location->name ?? $asset->location_detail ?? '—' }}
        @break
    @case('department')
        {{ $asset->department ?? '—' }}
        @break
    @case('custodian')
        {{ $asset->custodian->name ?? 'Unassigned' }}
        @break
    @case('status')
        @if($plain)
            {{ $asset->assetStatus->name ?? 'Unknown' }}
        @else
            <span class="badge bg-{{ $asset->assetStatus->badge_color ?? 'secondary' }}">{{ $asset->assetStatus->name ?? 'Unknown' }}</span>
        @endif
        @break
    @case('purchase_date')
        {{ optional($asset->purchase_date)->format('d M Y') ?? '—' }}
        @break
    @case('purchase_price')
        {{ $asset->purchase_price !== null ? number_format((float) $asset->purchase_price, 2) : '—' }}
        @break
    @case('supplier')
        {{ $asset->supplier ?? '—' }}
        @break
    @case('warranty_expiry_date')
        {{ optional($asset->warranty_expiry_date)->format('d M Y') ?? '—' }}
        @break
@endswitch
