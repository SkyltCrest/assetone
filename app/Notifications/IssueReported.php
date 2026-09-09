<?php

namespace App\Notifications;

use App\Models\IssueReport;
use Illuminate\Notifications\Notification;

class IssueReported extends Notification
{
    public function __construct(public IssueReport $report)
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
        $asset = $this->report->asset;

        return [
            'type' => 'issue_reported',
            'issue_id' => $this->report->id,
            'asset_code' => $asset->asset_code ?? '',
            'asset_name' => $asset->name ?? '',
            'actor' => $this->report->reporter->name ?? 'A staff member',
            'title' => 'New reported issue to verify',
            'message' => sprintf(
                '%s reported an issue with %s (%s). Please verify the asset before recording maintenance.',
                $this->report->reporter->name ?? 'A staff member',
                $asset->name ?? 'an asset',
                $asset->asset_code ?? '—'
            ),
            'url' => route('issue-verifications.show', $this->report->id),
        ];
    }
}
