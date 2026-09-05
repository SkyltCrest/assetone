<?php

namespace App\Notifications;

use App\Models\AssetAssignment;
use Illuminate\Notifications\Notification;

class AssignmentAccepted extends Notification
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

        return [
            'type' => 'assignment_accepted',
            'assignment_id' => $this->assignment->id,
            'asset_code' => $asset->asset_code ?? '',
            'asset_name' => $asset->name ?? '',
            'actor' => $this->assignment->custodian->name ?? 'The staff member',
            'title' => 'Assignment accepted',
            'message' => sprintf(
                '%s accepted the assignment of %s (%s). The asset is now officially in their custody.',
                $this->assignment->custodian->name ?? 'The staff member',
                $asset->name ?? 'the asset',
                $asset->asset_code ?? '—'
            ),
            'url' => route('assignments.index'),
        ];
    }
}
