@extends('layouts.app')

@section('title', 'Asset Registration')
@section('heading', 'Asset Registration')
@section('subheading', 'Register a new asset into AssetOne')

@section('content')

<x-banner title="Register New Asset" text="Enter the asset information below to register a new asset." chip="Asset code is auto-generated" />

<form method="POST" action="{{ route('assets.store') }}" enctype="multipart/form-data">
    @csrf
    @include('assets._form', ['submitLabel' => 'Save Asset'])
</form>

{{-- Shown once, straight after an asset has been registered --}}
@if($registered ?? null)
<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-qr-code me-2"></i>Asset QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="alert alert-success py-2"><i class="bi bi-check-circle-fill me-2"></i>Asset has been registered successfully.</div>
                <div class="qrbox d-inline-block bg-white rounded-3 p-2 mb-3">
                    <img src="{{ route('assets.qr', $registered) }}" alt="QR code for {{ $registered->asset_code }}" width="220" height="220" id="qrImage">
                </div>
                <div class="qr-info text-start">
                    <small class="text-muted">Asset Code</small>
                    <h5 class="fw-bold mb-2">{{ $registered->asset_code }}</h5>
                    <small class="text-muted">Asset Name</small>
                    <div class="fw-semibold mb-2">{{ $registered->name }}</div>
                    <small class="text-muted">Serial Number</small>
                    <div class="fw-semibold">{{ $registered->serial_number }}</div>
                </div>
                <small class="text-muted d-block mt-3">Scanning the QR code opens this asset in AssetOne.</small>
            </div>
            <div class="modal-footer">
                <a href="{{ route('assets.show', $registered) }}" class="btn btn-secondary">View Asset</a>
                <button type="button" class="btn btn-outline-primary" data-qr-download data-qr-src="{{ route('assets.qr', $registered) }}" data-code="{{ $registered->asset_code }}" data-name="{{ $registered->name }}" data-serial="{{ $registered->serial_number }}"><i class="bi bi-download me-2"></i>Download QR</button>
                <button type="button" class="btn btn-primary" data-qr-print data-qr-src="{{ route('assets.qr', $registered) }}" data-code="{{ $registered->asset_code }}" data-name="{{ $registered->name }}" data-serial="{{ $registered->serial_number }}"><i class="bi bi-printer me-2"></i>Print QR</button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>bootstrap.Modal.getOrCreateInstance(document.getElementById('qrModal')).show();</script>
@endpush
@endif

@endsection
