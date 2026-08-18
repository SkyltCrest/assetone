@extends('layouts.app')

@section('title', 'Asset Registration')
@section('heading', 'Asset Registration')
@section('subheading', 'Register a new asset into the system')

@section('content')

<div class="mb-4">
    <h3 class="fw-bold">Register New Asset</h3>
    <p class="text-muted">Enter the details below to register a new asset.</p>
</div>

<form method="POST" action="{{ route('assets.store') }}">
    @csrf

    @include('assets._form')

    <div class="d-flex justify-content-end gap-2 mb-5">
        <a href="{{ route('home') }}" class="btn btn-secondary px-4">Cancel</a>
        <button type="reset" class="btn btn-outline-secondary px-4">Reset</button>
        <button type="submit" class="btn btn-save px-4"><i class="bi bi-save me-2"></i>Save Asset</button>
    </div>
</form>

@endsection
