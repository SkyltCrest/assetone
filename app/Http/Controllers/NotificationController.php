<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationController extends Controller
{
    /** Kinds of action offered in the "All actions" filter: key => [label, icon]. */
    private const ACTIONS = [
        'create' => ['Created', 'bi-plus-circle'],
        'update' => ['Updated', 'bi-pencil-square'],
        'delete' => ['Deleted', 'bi-trash3'],
        'assign' => ['Assigned', 'bi-person-check'],
        'return' => ['Returned', 'bi-arrow-return-left'],
        'status' => ['Status change', 'bi-arrow-repeat'],
        'alert' => ['Alert', 'bi-exclamation-triangle'],
    ];

    private const PERIODS = ['today' => 'Today', '7' => 'Last 7 days', '30' => 'Last 30 days'];

    public function index(Request $request): View
    {
        $user = $request->user();
        $all = $this->describe($user->notifications()->get());
        $filtered = $this->filter($all, $request);

        $page = max(1, (int) $request->query('page', 1));
        $perPage = 20;
        $notifications = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => route('notifications.index'), 'query' => $request->query()],
        );

        $weekAgo = now()->subDays(7);

        return view('notifications.index', [
            'notifications' => $notifications,
            'modules' => $all->countBy('module')->sortDesc(),
            'actions' => self::ACTIONS,
            'periods' => self::PERIODS,
            'module' => (string) $request->query('module', ''),
            'action' => (string) $request->query('action', ''),
            'period' => (string) $request->query('period', ''),
            'search' => (string) $request->query('search', ''),
            'unreadOnly' => $request->boolean('unread'),
            'totalCount' => $all->count(),
            'unreadCount' => $all->where('read', false)->count(),
            'todayCount' => $all->filter(fn ($n) => $n->at->isToday())->count(),
            'weekCount' => $all->filter(fn ($n) => $n->at->gte($weekAgo))->count(),
            'alertCount' => $all->where('action', 'alert')->count(),
            'peopleToday' => $all->filter(fn ($n) => $n->at->isToday())->pluck('actor')->filter()->unique()->count(),
            'modulesThisWeek' => $all->filter(fn ($n) => $n->at->gte($weekAgo))->pluck('module')->unique()->count(),
        ]);
    }

    /**
     * Download the notifications that match the current filters.
     */
    public function export(Request $request): StreamedResponse
    {
        $rows = $this->filter($this->describe($request->user()->notifications()->get()), $request);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Time', 'Module', 'Action', 'Performed by', 'Role', 'Summary', 'Details', 'Changes', 'Read']);

            foreach ($rows as $n) {
                fputcsv($out, [
                    $n->at->format('Y-m-d H:i:s'),
                    $n->module,
                    self::ACTIONS[$n->action][0] ?? $n->action,
                    $n->actor,
                    $n->actorRole,
                    $n->title,
                    $n->message,
                    collect($n->changes)->map(fn ($c) => "{$c['field']}: {$c['from']} -> {$c['to']}")->implode('; '),
                    $n->read ? 'Yes' : 'No',
                ]);
            }

            fclose($out);
        }, 'asset-activity-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
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

    /**
     * Flip one notification between read and unread without leaving the page.
     */
    public function toggle(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->read_at ? $notification->markAsUnread() : $notification->markAsRead();

        return back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'All notifications marked as read.');
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $request->user()->notifications()->findOrFail($id)->delete();

        return back()->with('status', 'Notification deleted.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->user()->notifications()->delete();

        return redirect()->route('notifications.index')->with('status', 'Activity history cleared.');
    }

    /**
     * Turn stored notifications into plain rows the page, filters and export share.
     *
     * @param  Collection<int, DatabaseNotification>  $notifications
     * @return Collection<int, object>
     */
    private function describe(Collection $notifications): Collection
    {
        return $notifications->map(function (DatabaseNotification $n) {
            $data = $n->data;
            $type = (string) ($data['type'] ?? '');

            return (object) [
                'id' => $n->id,
                'at' => $n->created_at,
                'read' => $n->read_at !== null,
                'title' => (string) ($data['title'] ?? 'Notification'),
                'message' => (string) ($data['message'] ?? ''),
                'url' => $data['url'] ?? null,
                'actor' => (string) ($data['actor'] ?? ''),
                'actorRole' => (string) ($data['actor_role'] ?? ''),
                'module' => (string) ($data['module'] ?? $this->moduleFor($type)),
                'action' => $this->actionFor($type, (string) ($data['action'] ?? '')),
                'changes' => array_values((array) ($data['changes'] ?? [])),
            ];
        })->values();
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return Collection<int, object>
     */
    private function filter(Collection $rows, Request $request): Collection
    {
        $module = (string) $request->query('module', '');
        $action = (string) $request->query('action', '');
        $period = (string) $request->query('period', '');
        $search = mb_strtolower(trim((string) $request->query('search', '')));

        $since = match (true) {
            $period === 'today' => today(),
            array_key_exists($period, self::PERIODS) => now()->subDays((int) $period),
            default => null,
        };

        return $rows
            ->when($module !== '', fn ($c) => $c->where('module', $module))
            ->when(array_key_exists($action, self::ACTIONS), fn ($c) => $c->where('action', $action))
            ->when($request->boolean('unread'), fn ($c) => $c->where('read', false))
            ->when($since, fn ($c) => $c->filter(fn ($n) => $n->at->gte($since)))
            ->when($search !== '', fn ($c) => $c->filter(fn ($n) => str_contains(
                mb_strtolower($n->title.' '.$n->message.' '.$n->module.' '.$n->actor.' '.(self::ACTIONS[$n->action][0] ?? '')),
                $search
            )))
            ->values();
    }

    private function moduleFor(string $type): string
    {
        return match (true) {
            str_starts_with($type, 'assignment_') => 'Assignment',
            str_starts_with($type, 'issue_') => 'Issues',
            default => 'Activity',
        };
    }

    private function actionFor(string $type, string $verb): string
    {
        return match (true) {
            $type === 'assignment_overdue_reminder' => 'alert',
            $type === 'assignment_awaiting_verification' => 'assign',
            $type === 'issue_reported' => 'create',
            str_starts_with($type, 'assignment_'), str_starts_with($type, 'issue_') => 'status',
            $verb === 'created' => 'create',
            $verb === 'deleted' => 'delete',
            $verb === 'marked as returned' => 'return',
            in_array($verb, ['accepted', 'rejected', 're-opened', 'resolved'], true) => 'status',
            default => 'update',
        };
    }
}
