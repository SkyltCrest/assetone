<?php

namespace App\Observers;

use App\Models\AssetAssignment;
use App\Models\IssueReport;
use App\Models\User;
use App\Notifications\AdminActivityNotification;
use App\Support\ActivityDescriptor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

/**
 * Notifies every administrator whenever a domain record is created, updated
 * or deleted. Registered for all the app's resource models in AppServiceProvider.
 */
class ActivityObserver
{
    /**
     * When true, changes are not reported. Used to silence internal cascades
     * (e.g. syncing an asset's custodian when an assignment is accepted).
     */
    public static bool $silent = false;

    /**
     * Attribute changes that never, on their own, warrant a notification.
     *
     * @var array<int, string>
     */
    private const IGNORED_ATTRIBUTES = ['updated_at', 'created_at', 'remember_token', 'qr_code_path'];

    /**
     * Run the given callback without emitting any activity notifications.
     */
    public static function silently(callable $callback): mixed
    {
        $previous = static::$silent;
        static::$silent = true;

        try {
            return $callback();
        } finally {
            static::$silent = $previous;
        }
    }

    public function created(Model $model): void
    {
        $this->notifyAdmins($model, 'created');
    }

    public function updated(Model $model): void
    {
        $changed = array_values(array_diff(array_keys($model->getChanges()), self::IGNORED_ATTRIBUTES));

        if ($changed === []) {
            return;
        }

        [$action, $detail] = $this->describeUpdate($model, $changed);

        $this->notifyAdmins($model, $action, $detail, changes: $this->changeList($model, $changed));
    }

    public function deleted(Model $model): void
    {
        $this->notifyAdmins($model, 'deleted', deleted: true);
    }

    /**
     * @return array{0: string, 1: string|null}  [action verb, detail text]
     */
    private function describeUpdate(Model $model, array $changed): array
    {
        if ($model instanceof AssetAssignment && in_array('status', $changed, true)) {
            $action = match ($model->status) {
                AssetAssignment::STATUS_ASSIGNED => 'accepted',
                AssetAssignment::STATUS_REJECTED => 'rejected',
                AssetAssignment::STATUS_PENDING => 're-opened',
                AssetAssignment::STATUS_UNASSIGNED => 'marked as returned',
                default => 'updated',
            };

            return [$action, null];
        }

        if ($model instanceof IssueReport && in_array('status', $changed, true)) {
            $action = match ($model->status) {
                IssueReport::STATUS_ACCEPTED => 'accepted',
                IssueReport::STATUS_REJECTED => 'rejected',
                IssueReport::STATUS_RESOLVED => 'resolved',
                default => 'updated',
            };

            return [$action, null];
        }

        return ['updated', 'changed: '.implode(', ', $changed)];
    }

    /**
     * Before / after values for the attributes that changed, ready to display.
     *
     * @param  list<string>  $changed
     * @return list<array{field: string, from: string, to: string}>
     */
    private function changeList(Model $model, array $changed): array
    {
        $show = function (mixed $value): string {
            if ($value === null || $value === '') {
                return '—';
            }
            if ($value instanceof \DateTimeInterface) {
                return $value->format('d M Y');
            }
            if (is_bool($value)) {
                return $value ? 'Yes' : 'No';
            }

            return \Illuminate\Support\Str::limit(is_scalar($value) ? (string) $value : json_encode($value), 80);
        };

        $list = [];
        foreach ($changed as $attribute) {
            if (in_array($attribute, ['password', 'remember_token'], true)) {
                $list[] = ['field' => 'Password', 'from' => '••••••', 'to' => 'changed'];

                continue;
            }

            $list[] = [
                'field' => ucwords(str_replace('_', ' ', preg_replace('/_id$/', '', $attribute))),
                'from' => $show($model->getOriginal($attribute)),
                'to' => $show($model->getAttribute($attribute)),
            ];
        }

        return $list;
    }

    private function moduleFor(Model $model): string
    {
        return match (true) {
            $model instanceof Asset => 'Registration',
            $model instanceof AssetAssignment => 'Assignment',
            $model instanceof AssetMaintenance => 'Maintenance',
            $model instanceof IssueReport => 'Issues',
            $model instanceof User => 'Users',
            default => 'Management',
        };
    }

    private function notifyAdmins(Model $model, string $action, ?string $detail = null, bool $deleted = false, array $changes = []): void
    {
        if (static::$silent) {
            return;
        }

        $actor = Auth::user();

        $admins = User::query()
            ->where('role', 'administrator')
            ->where('status', 'active')
            ->when($actor, fn ($q) => $q->where('id', '!=', $actor->id))
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new AdminActivityNotification(
            action: $action,
            subject: ActivityDescriptor::subject($model),
            actorName: $actor?->name ?? 'The system',
            url: ActivityDescriptor::url($model, $deleted),
            detail: $detail,
            actorRole: $actor ? ucwords(str_replace('_', ' ', $actor->role)) : null,
            module: $this->moduleFor($model),
            changes: $changes,
        ));
    }
}
