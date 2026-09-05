<?php

namespace App\Observers;

use App\Models\AssetAssignment;
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

        $this->notifyAdmins($model, $action, $detail);
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

        return ['updated', 'changed: '.implode(', ', $changed)];
    }

    private function notifyAdmins(Model $model, string $action, ?string $detail = null, bool $deleted = false): void
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
        ));
    }
}
