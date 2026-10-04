{{--
    Page banner used at the top of every page.
      title   heading text
      text    one-line description
      keys    keyboard hint, e.g. ['N' => 'new', '/' => 'search']
      chip    plain text chip (used instead of keys)
    The slot holds the action buttons shown on the right.
--}}
@props(['title', 'text' => null, 'keys' => [], 'chip' => null, 'chipIcon' => 'bi-magic'])

<div class="page-banner mb-4 animate-in delay-2">
    <div class="page-banner-bg" aria-hidden="true"></div>
    <div class="page-banner-content d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold mb-1">{{ $title }}</h3>
            @if($text)<p class="mb-0">{!! $text instanceof \Illuminate\View\ComponentSlot ? $text : e($text) !!}</p>@endif
        </div>
        @if($keys || $chip || trim($slot))
            <div class="banner-actions no-print">
                @if($keys)
                    <div class="banner-chip key-hint d-none d-lg-inline-flex">
                        <i class="bi bi-keyboard"></i>
                        @foreach($keys as $key => $label)
                            @unless($loop->first)<span>&middot;</span>@endunless
                            <span><kbd>{{ $key }}</kbd> {{ $label }}</span>
                        @endforeach
                    </div>
                @elseif($chip)
                    <div class="banner-chip"><i class="bi {{ $chipIcon }}"></i><span data-banner-chip>{{ $chip }}</span></div>
                @endif
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
