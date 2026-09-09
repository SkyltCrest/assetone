<?php

namespace App\Notifications;

use App\Models\IssueReport;
use Illuminate\Notifications\Notification;

class IssueReportRejected extends Notification
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
        $reason = trim((string) $this->report->rejection_reason);

        return [
            'type' => 'issue_report_rejected',
            'issue_id' => $this->report->id,
            'asset_code' => $asset->asset_code ?? '',
            'asset_name' => $asset->name ?? '',
            'actor' => $this->report->verifier->name ?? 'The asset officer',
            'reason' => $reason,
            'title' => 'Reported issue rejected',
            'message' => sprintf(
                '%s tested %s (%s) and found it working properly, so the reported issue was rejected.%s',
                $this->report->verifier->name ?? 'The asset officer',
                $asset->name ?? 'the asset',
                $asset->asset_code ?? '—',
                $reason !== '' ? ' Note: "'.$reason.'".' : ''
            ),
            'url' => route('issues.show', $this->report->id),
        ];
    }
}
