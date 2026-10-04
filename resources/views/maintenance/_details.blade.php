{{-- Read-only details pop-up for one maintenance record. --}}
@php
    [$dueLabel, $dueColor] = $record->dueStatus();
    $statusBadges = ['pending' => 'warning', 'in_progress' => 'info', 'completed' => 'success', 'cancelled' => 'secondary'];
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Maintenance Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    @if($record->asset?->photoUrl())
                        <img src="{{ $record->asset->photoUrl() }}" class="view-asset-thumb" alt="{{ $record->asset->name }}" data-lightbox="{{ $record->asset->photoUrl() }}" data-lb-name="{{ $record->asset->name }}" data-lb-info="{{ $record->asset->asset_code }}">
                    @else
                        <div class="asset-thumb-placeholder" style="width:70px;height:70px;font-size:26px;"><i class="bi bi-box-seam"></i></div>
                    @endif
                    <div><div class="fw-bold fs-5" style="color:var(--hd)">{{ $record->asset->name ?? '—' }}</div><span class="text-muted small">{{ $record->maintenance_code }}</span></div>
                </div>
                <div class="detail-row"><span class="label">Maintenance ID</span><span class="value">{{ $record->maintenance_code }}</span></div>
                <div class="detail-row"><span class="label">Asset ID</span><span class="value">{{ $record->asset->asset_code ?? '—' }}</span></div>
                <div class="detail-row"><span class="label">Asset Name</span><span class="value">{{ $record->asset->name ?? '—' }}</span></div>
                <div class="detail-row"><span class="label">Maintenance Type</span><span class="value">{{ $types[$record->type] ?? $record->type }}</span></div>
                <div class="detail-row"><span class="label">Maintenance Date</span><span class="value">{{ $record->maintenance_date->format('d F Y') }}</span></div>
                <div class="detail-row"><span class="label">Next Maintenance</span><span class="value">{{ optional($record->next_maintenance_date)->format('d F Y') ?? '—' }}</span></div>
                <div class="detail-row"><span class="label">Service Provider</span><span class="value">{{ $record->service_provider }}</span></div>
                <div class="detail-row"><span class="label">Cost</span><span class="value">{{ $record->cost !== null ? 'RM '.number_format($record->cost, 2) : '—' }}</span></div>
                <div class="detail-row"><span class="label">Status</span><span class="value"><span class="badge bg-{{ $statusBadges[$record->status] ?? 'secondary' }}">{{ $statuses[$record->status] ?? $record->status }}</span></span></div>
                <div class="detail-row"><span class="label">Due Status</span><span class="value"><span class="badge bg-{{ $dueColor }}">{{ $dueLabel }}</span></span></div>
                <div class="detail-row"><span class="label">Notes</span><span class="value" style="white-space:pre-line">{{ $record->description ?: '—' }}</span></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                @if($record->asset)
                    <a href="{{ route('assets.show', $record->asset) }}" class="btn btn-outline-primary"><i class="bi bi-box-arrow-up-right me-1"></i>Open Asset</a>
                @endif
            </div>
        </div>
    </div>
</div>
