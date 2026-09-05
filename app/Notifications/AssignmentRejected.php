<?php

namespace App\Notifications;

use App\Models\AssetAssignment;
use Illuminate\Notifications\Notification;

class AssignmentRejected extends Notification
{
    public function __construct(public AssetAssignment $assignment)
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
        $reason = trim((string) $this->assignment->rejection_reason);

        return [
            'type' => 'assignment_rejected',
            'assignment_id' => $this->assignment->id,
            'asset_code' => $asset->asset_code ?? '',
            'asset_name' => $asset->name ?? '',
            'actor' => $this->assignment->custodian->name ?? 'The staff member',
            'reason' => $reason,
            'title' => 'Assignment rejected',
            'message' => sprintf(
                '%s rejected the assignment of %s (%s).%s Please create the assignment again with the correct information.',
                $this->assignment->custodian->name ?? 'The staff member',
                $asset->name ?? 'the asset',
                $asset->asset_code ?? '—',
                $reason !== '' ? ' Reason: "'.$reason.'".' : ''
            ),
            'url' => route('assignments.index'),
        ];
    }
}
