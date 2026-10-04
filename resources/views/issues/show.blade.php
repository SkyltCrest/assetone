@extends('layouts.app')

@section('title', 'Reported Issue')
@section('heading', 'Reported Issue')
@section('subheading', $report->report_code)

@section('content')

@php
    $rejected = $report->status === \App\Models\IssueReport::STATUS_REJECTED;
    $accepted = in_array($report->status, [\App\Models\IssueReport::STATUS_ACCEPTED, \App\Models\IssueReport::STATUS_RESOLVED]);
    $resolved = $report->status === \App\Models\IssueReport::STATUS_RESOLVED;
@endphp

<x-banner :title="$report->asset->name ?? 'Asset'">
    <x-slot:text>Report {{ $report->report_code }} &middot; Asset ID: {{ $report->asset->asset_code ?? '—' }}</x-slot:text>
    <a href="{{ route('issues.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
</x-banner>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="content-card mb-4">
            <h5 class="mb-3">Report Details</h5>
            <div class="detail-row"><span class="label">Status</span><span class="value"><span class="badge bg-{{ $report->statusColor() }}">{{ $report->statusLabel() }}</span></span></div>
            <div class="detail-row"><span class="label">Reported On</span><span class="value">{{ $report->created_at->format('d F Y, g:i A') }}</span></div>
            <div class="detail-row"><span class="label">Asset</span><span class="value">{{ $report->asset->asset_code ?? '—' }} — {{ $report->asset->name ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Current Asset Status</span><span class="value">{{ $report->asset->assetStatus->name ?? '—' }}</span></div>
            <div class="detail-row"><span class="label">Reported Fault</span><span class="value" style="max-width:60%;">{{ $report->description }}</span></div>
            @if($report->verified_at)
                <div class="detail-row"><span class="label">Verified By</span><span class="value">{{ $report->verifier->name ?? '—' }} on {{ $report->verified_at->format('d F Y, g:i A') }}</span></div>
            @endif
            @if($rejected && $report->rejection_reason)
                <div class="detail-row"><span class="label">Reason for Rejection</span><span class="value text-danger" style="max-width:60%;">{{ $report->rejection_reason }}</span></div>
            @endif
        </div>


        @if($report->photos->isNotEmpty())
            <div class="content-card mb-4">
                <h5 class="mb-3">Photos of the Damage</h5>
                <div class="photo-grid">
                    @foreach($report->photos as $photo)
                        <img src="{{ $photo->url() }}" data-lightbox="{{ $photo->url() }}" data-lb-name="{{ $report->report_code }} · photo {{ $loop->iteration }}" alt="Damage photo {{ $loop->iteration }}" loading="lazy">
                    @endforeach
                </div>
            </div>
        @endif

        @if($report->maintenance)
            <div class="content-card">
                <h5 class="mb-3">Maintenance</h5>
                <div class="detail-row"><span class="label">Maintenance ID</span><span class="value">{{ $report->maintenance->maintenance_code }}</span></div>
                <div class="detail-row"><span class="label">Type</span><span class="value">{{ ucfirst($report->maintenance->type) }}</span></div>
                <div class="detail-row"><span class="label">Service Provider</span><span class="value">{{ $report->maintenance->service_provider }}</span></div>
                <div class="detail-row"><span class="label">Started</span><span class="value">{{ $report->maintenance->maintenance_date->format('d F Y') }}</span></div>
                <div class="detail-row"><span class="label">Maintenance Status</span><span class="value">{{ ucwords(str_replace('_', ' ', $report->maintenance->status)) }}</span></div>
            </div>
        @endif
    </div>

    <div class="col-lg-5">
        <div class="content-card">
            <h5 class="mb-2">Progress</h5>
            <div class="step done">
                <div class="dot"><i class="bi bi-check-lg"></i></div>
                <div>
                    <div class="step-title">Issue reported</div>
                    <div class="step-sub">{{ $report->created_at->format('d M Y, g:i A') }}</div>
                </div>
            </div>

            @if($rejected)
                <div class="step rejected">
                    <div class="dot"><i class="bi bi-x-lg"></i></div>
                    <div>
                        <div class="step-title">Rejected by asset officer</div>
                        <div class="step-sub">The asset was tested and found to be working properly.</div>
                    </div>
                </div>
            @else
                <div class="step {{ $accepted ? 'done' : 'current' }}">
                    <div class="dot">{{ $accepted ? '' : '2' }}@if($accepted)<i class="bi bi-check-lg"></i>@endif</div>
                    <div>
                        <div class="step-title">Verified &amp; accepted</div>
                        <div class="step-sub">{{ $accepted ? 'Confirmed damaged. Asset moved to Under Maintenance.' : 'Waiting for an asset officer to test the asset.' }}</div>
                    </div>
                </div>

                <div class="step {{ $resolved ? 'done' : '' }}">
                    <div class="dot">{{ $resolved ? '' : '3' }}@if($resolved)<i class="bi bi-check-lg"></i>@endif</div>
                    <div>
                        <div class="step-title">Maintenance completed</div>
                        <div class="step-sub">{{ $resolved ? 'Asset is back In Use.' : 'Asset returns to In Use once repairs are done.' }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
