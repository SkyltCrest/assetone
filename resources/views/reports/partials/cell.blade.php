@php
    /** @var \App\Models\Asset $asset */
@endphp
@switch($column)
    @case('asset_code')
        <span class="fw-semibold">{{ $asset->asset_code }}</span>
        @break
    @case('name')
        {{ $asset->name }}
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
        <span class="badge bg-{{ $asset->assetStatus->badge_color ?? 'secondary' }}">{{ $asset->assetStatus->name ?? 'Unknown' }}</span>
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
