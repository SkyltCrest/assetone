<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Filter tabs: label and the prefix of the notification "type" they cover.
     * (See the toArray() of each class in App\Notifications.)
     */
    private const FILTERS = [
        'all' => ['All', null],
        'unread' => ['Unread', null],
        'assignment' => ['Assignments', 'assignment_'],
        'issue' => ['Issues', 'issue_'],
        'activity' => ['Activity', 'admin_activity'],
    ];

    public function index(Request $request): View
    {
        $user = $request->user();
        $filter = array_key_exists($request->query('filter'), self::FILTERS) ? $request->query('filter') : 'all';
        $prefix = self::FILTERS[$filter][1];

        $notifications = $user->notifications()
            ->when($filter === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when($prefix, fn ($q) => $q->where('data', 'like', '%"type":"'.$prefix.'%'))
            ->paginate(20)
            ->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'filter' => $filter,
            'filters' => array_map(fn ($option) => $option[0], self::FILTERS),
            'totalCount' => $user->notifications()->count(),
            'unreadCount' => $user->unreadNotifications()->count(),
            'todayCount' => $user->notifications()->whereDate('created_at', today())->count(),
            'weekCount' => $user->notifications()->where('created_at', '>=', now()->subDays(7))->count(),
        ]);
    }

    /**
     * Mark a single notification as read, then continue to its target page.
     */
    public function read(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $url = $notification->data['url'] ?? route('notifications.index');

        return redirect()->to($url);
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'All notifications marked as read.');
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $request->user()->notifications()->findOrFail($id)->delete();

        return back()->with('status', 'Notification removed.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->user()->notifications()->delete();

        return redirect()->route('notifications.index')->with('status', 'All notifications cleared.');
    }
}
