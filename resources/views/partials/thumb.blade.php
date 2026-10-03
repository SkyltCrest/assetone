{{-- Small square picture for table rows. $url may be null. --}}
@if($url ?? null)
    <a href="{{ $url }}" target="_blank" rel="noopener" title="Open picture"><img src="{{ $url }}" alt="{{ $alt ?? 'Picture' }}" class="thumb" loading="lazy"></a>
@else
    <span class="thumb thumb-empty" title="No picture"><i class="bi bi-image"></i></span>
@endif
