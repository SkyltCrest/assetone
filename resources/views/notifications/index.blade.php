@extends('layouts.app')

@section('title', 'Notifications')
@section('heading', 'Notifications')
@section('subheading', 'Your recent alerts and updates')

@section('content')

@php
    // Icon and colour per kind of notification.
    $look = function (array $data): array {
        $type = $data['type'] ?? '';

        return match (true) {
            str_starts_with($type, 'assignment_') => ['bi-person-check', 'green'],
            str_starts_with($type, 'issue_') => ['bi-exclamation-triangle', 'orange'],
            ($data['action'] ?? '') === 'deleted' => ['bi-trash', 'red'],
            default => ['bi-activity', 'blue'],
        };
    };
@endphp

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Notifications</h3>
        <p class="text-muted mb-0">Assignment requests, reported issues and activity across the system.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.readAll') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-outline-primary"><i class="bi bi-check2-all me-1"></i> Mark all read</button>
            </form>
        @endif
        @if($totalCount > 0)
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#clearNotificationsModal"><i class="bi bi-trash me-1"></i> Clear all</button>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Total Activity</div><h2>{{ number_format($totalCount) }}</h2></div>
        <div class="stat-icon icon-blue"><i class="bi bi-bell"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Unread</div><h2>{{ number_format($unreadCount) }}</h2></div>
        <div class="stat-icon icon-red"><i class="bi bi-envelope"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Today</div><h2>{{ number_format($todayCount) }}</h2></div>
        <div class="stat-icon icon-green"><i class="bi bi-calendar-day"></i></div>
    </div></div>
    <div class="col-6 col-xl-3"><div class="stat-card d-flex justify-content-between align-items-start">
        <div><div class="text-muted small text-uppercase">Last 7 Days</div><h2>{{ number_format($weekCount) }}</h2></div>
        <div class="stat-icon icon-orange"><i class="bi bi-calendar-week"></i></div>
    </div></div>
</div>

<div class="card content-card p-4">
    <div class="chips mb-3">
        @foreach($filters as $value => $label)
            <a href="{{ route('notifications.index', $value === 'all' ? [] : ['filter' => $value]) }}" class="chip {{ $filter === $value ? 'active' : '' }}">
                {{ $label }}@if($value === 'unread' && $unreadCount > 0)<b>{{ $unreadCount }}</b>@endif
            </a>
        @endforeach
    </div>

    <div class="nt-list">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data;
                [$icon, $color] = $look($data);
            @endphp
            <div class="nt-item {{ $notification->read_at ? '' : 'unread' }}">
                <div class="stat-icon icon-{{ $color }} nt-icon"><i class="bi {{ $icon }}"></i></div>
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="nt-main m-0">
                    @csrf
                    <button type="submit" class="nt-open">
                        <span class="nt-title">
                            @if(! $notification->read_at)<span class="badge bg-primary me-1">New</span>@endif
                            {{ $data['title'] ?? 'Notification' }}
                        </span>
                        <span class="nt-text">{{ $data['message'] ?? '' }}</span>
                        <span class="nt-time" title="{{ $notification->created_at->format('d M Y, g:i A') }}">{{ $notification->created_at->diffForHumans() }}</span>
                    </button>
                </form>
                <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Remove notification" aria-label="Remove notification"><i class="bi bi-x-lg"></i></button>
                </form>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="bi bi-bell-slash display-4 text-muted"></i>
                <h5 class="mt-3">{{ $filter === 'all' ? 'No notifications' : 'Nothing here' }}</h5>
                <p class="text-muted">{{ $filter === 'all' ? "You'll see assignment updates and alerts here." : 'No notifications match this filter.' }}</p>
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $notifications->links() }}
    </div>
</div>

<div class="modal fade" id="clearNotificationsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Clear all notifications?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                All {{ number_format($totalCount) }} of your notifications, read and unread, will be removed. This cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('notifications.clear') }}" class="m-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i>Clear All</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
