{{--
    Small square picture for table rows; opens in the lightbox when clicked.
    $url may be null. $name / $info caption the lightbox.
--}}
@if($url ?? null)
    <img src="{{ $url }}" alt="{{ $alt ?? 'Picture' }}" class="thumb asset-thumb" loading="lazy"
         data-lightbox="{{ $url }}" data-lb-name="{{ $name ?? ($alt ?? '') }}" data-lb-info="{{ $info ?? '' }}">
@else
    <span class="thumb thumb-empty" title="No picture"><i class="bi bi-image"></i></span>
@endif
