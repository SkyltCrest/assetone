@extends('layouts.app')

@section('title', 'Update Asset')
@section('heading', 'Update Asset')
@section('subheading', 'Edit the information of an existing asset')

@section('content')

<x-banner title="Update Asset" text="Correct or update the asset information below, then save your changes." :chip="$asset->asset_code" />

<form method="POST" action="{{ route('assets.update', $asset) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('assets._form', ['submitLabel' => 'Update Asset'])
</form>

@endsection
