<?php

namespace App\Notifications;

use App\Models\AssetAssignment;
use Illuminate\Notifications\Notification;

class AssignmentAwaitingVerification extends Notification
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
            'type' => 'assignment_awaiting_verification',
            'assignment_id' => $this->assignment->id,
            'asset_code' => $asset->asset_code ?? '',
            'asset_name' => $asset->name ?? '',
            'actor' => $this->assignment->assignedBy->name ?? 'An asset officer',
            'title' => 'New asset assignment to verify',
            'message' => sprintf(
                '%s assigned %s (%s) to you. Please verify that the asset you received matches the record.',
                $this->assignment->assignedBy->name ?? 'An asset officer',
                $asset->name ?? 'an asset',
                $asset->asset_code ?? '—'
            ),
            'url' => route('my-assignments.show', $this->assignment->id),
        ];
    }
}
