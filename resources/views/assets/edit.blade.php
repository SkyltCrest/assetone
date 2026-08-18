@extends('layouts.app')

@section('title', 'Edit Asset')
@section('heading', 'Asset Registration')
@section('subheading', 'Update an existing asset record')

@section('content')

<div class="mb-4">
    <h3 class="fw-bold">Edit Asset — {{ $asset->asset_code }}</h3>
    <p class="text-muted">Update the details below and save your changes.</p>
</div>

<form method="POST" action="{{ route('assets.update', $asset) }}">
    @csrf
    @method('PUT')

    @include('assets._form')

    <div class="d-flex justify-content-end gap-2 mb-5">
        <a href="{{ route('assets.show', $asset) }}" class="btn btn-secondary px-4">Cancel</a>
        <button type="submit" class="btn btn-save px-4"><i class="bi bi-save me-2"></i>Save Changes</button>
    </div>
</form>

@endsection
