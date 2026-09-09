<?php

namespace App\Notifications;

use App\Models\IssueReport;
use Illuminate\Notifications\Notification;

class IssueReportResolved extends Notification
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
            'type' => 'issue_report_resolved',
            'issue_id' => $this->report->id,
            'asset_code' => $asset->asset_code ?? '',
            'asset_name' => $asset->name ?? '',
            'title' => 'Reported issue resolved',
            'message' => sprintf(
                'Maintenance for %s (%s) is complete. The asset is back In Use.',
                $asset->name ?? 'the asset',
                $asset->asset_code ?? '—'
            ),
            'url' => route('issues.show', $this->report->id),
        ];
    }
}
