<?php

namespace App\Notifications;

use App\Models\AssetAssignment;
use Illuminate\Notifications\Notification;

class AssignmentOverdueReminder extends Notification
{
    public function __construct(public AssetAssignment $assignment, public string $sentBy)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $asset = $this->assignment->asset;
        $daysLate = (int) $this->assignment->due_date->diffInDays(today());

        return [
            'type' => 'assignment_overdue_reminder',
            'assignment_id' => $this->assignment->id,
            'asset_code' => $asset->asset_code ?? '',
            'asset_name' => $asset->name ?? '',
            'actor' => $this->sentBy,
            'title' => 'Asset overdue for return',
            'message' => sprintf(
                '%s (%s) was due back on %s and is %d day(s) overdue. Please return it or contact %s.',
                $asset->name ?? 'An asset',
                $asset->asset_code ?? '—',
                $this->assignment->due_date->format('d M Y'),
                $daysLate,
                $this->sentBy
            ),
            'url' => route('my-assignments.show', $this->assignment->id),
        ];
    }
}
