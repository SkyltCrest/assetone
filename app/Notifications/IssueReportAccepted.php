<?php

namespace App\Notifications;

use App\Models\IssueReport;
use Illuminate\Notifications\Notification;

class IssueReportAccepted extends Notification
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
            'type' => 'issue_report_accepted',
            'issue_id' => $this->report->id,
            'asset_code' => $asset->asset_code ?? '',
            'asset_name' => $asset->name ?? '',
            'actor' => $this->report->verifier->name ?? 'The asset officer',
            'title' => 'Reported issue accepted',
            'message' => sprintf(
                '%s confirmed the issue with %s (%s). The asset has been moved to Under Maintenance while it is repaired.',
                $this->report->verifier->name ?? 'The asset officer',
                $asset->name ?? 'the asset',
                $asset->asset_code ?? '—'
            ),
            'url' => route('issues.show', $this->report->id),
        ];
    }
}
