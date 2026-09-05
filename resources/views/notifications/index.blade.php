@extends('layouts.app')

@section('title', 'Notifications')
@section('heading', 'Notifications')
@section('subheading', 'Your recent alerts and updates')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Notifications</h3>
        <p class="text-muted mb-0">{{ $unreadCount }} unread</p>
    </div>
    @if($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.readAll') }}">
            @csrf
            <button type="submit" class="btn btn-outline-primary"><i class="bi bi-check2-all me-1"></i> Mark all read</button>
        </form>
    @endif
</div>

<div class="card content-card p-0">
    <div class="list-group list-group-flush">
        @forelse($notifications as $notification)
            @php $data = $notification->data; @endphp
            <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="m-0">
                @csrf
                <button type="submit" class="list-group-item list-group-item-action border-0 text-start w-100 py-3 {{ $notification->read_at ? '' : 'bg-light' }}">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <div class="fw-bold mb-1">
                                @if(!$notification->read_at)<span class="badge bg-primary me-1">New</span>@endif
                                {{ $data['title'] ?? 'Notification' }}
                            </div>
                            <div class="small text-muted">{{ $data['message'] ?? '' }}</div>
                        </div>
                        <small class="text-muted text-nowrap">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                </button>
            </form>
        @empty
            <div class="text-center py-5">
                <i class="bi bi-bell-slash display-4 text-muted"></i>
                <h5 class="mt-3">No notifications</h5>
                <p class="text-muted">You'll see assignment updates and alerts here.</p>
            </div>
        @endforelse
    </div>
</div>

<div class="mt-3">
    {{ $notifications->links() }}
</div>

@endsection
