{{-- A user's profile picture, or their initials when they have none. $size is in pixels. --}}
@php $size = $size ?? 38; @endphp
<span class="avatar" style="--size:{{ $size }}px">
    @if($user->photo)
        <img src="{{ $user->photoUrl() }}" alt="{{ $user->name }}" loading="lazy">
    @else
        {{ $user->initials() }}
    @endif
</span>
