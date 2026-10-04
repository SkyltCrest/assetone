{{--
    Photo picker: upload or take a picture, previewed before the form is sent.

    $name      form field name (sent as an array when $max > 1)
    $max       how many pictures may be attached (default 1)
    $required  whether at least one new picture must be chosen
    $current   URL of the picture already stored, if any
    $round     show the preview as a circle (profile pictures)
--}}
@php
    $max = $max ?? 1;
    $required = $required ?? false;
    $current = $current ?? null;
    $round = $round ?? false;
    $crop = $crop ?? null;        // '4:3' crops every picture to 800 x 600
    $maxMb = $maxMb ?? null;     // reject files larger than this before upload
    $errorKey = $max > 1 ? $name.'.*' : $name;
@endphp

<div class="photo-picker {{ $round ? 'photo-picker-round' : '' }}" data-photo-picker data-max="{{ $max }}" data-required="{{ $required ? 1 : 0 }}" @if($crop) data-crop="{{ $crop }}" @endif @if($maxMb) data-max-mb="{{ $maxMb }}" @endif>
    <input type="file" name="{{ $name }}{{ $max > 1 ? '[]' : '' }}" class="d-none" accept="image/*" data-photo-input @if($max > 1) multiple @endif>

    <div class="photo-preview" data-photo-preview>
        @if($current)
            <div class="photo-thumb"><img src="{{ $current }}" alt="Current photo"></div>
        @else
            <div class="photo-empty">
                <i class="bi bi-image"></i>
                <span>{{ $max > 1 ? "No photos yet (up to {$max})" : 'No photo yet' }}</span>
            </div>
        @endif
    </div>

    <div class="d-flex flex-wrap gap-2 mt-2 {{ $round ? 'justify-content-center' : '' }}">
        <button type="button" class="btn btn-outline-primary btn-sm" data-photo-upload><i class="bi bi-upload me-1"></i>Upload</button>
        <button type="button" class="btn btn-outline-primary btn-sm" data-photo-camera><i class="bi bi-camera me-1"></i>Camera</button>
    </div>

    <div class="photo-error small mt-2 d-none" data-photo-error></div>
    @error($name)<div class="photo-error small mt-2">{{ $message }}</div>@enderror
    @if($max > 1)
        @error($errorKey)<div class="photo-error small mt-2">{{ $message }}</div>@enderror
    @endif
</div>
