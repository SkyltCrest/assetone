<?php

namespace App\Observers;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\IssueReport;
use App\Notifications\IssueReportResolved;

/**
 * Keeps an asset's own status ("Active" / "Under Maintenance") in step with
 * its maintenance records, so the status never has to be changed by hand on
 * top of logging the maintenance work.
 *
 * Also closes the loop on a reported issue: once the maintenance opened for
 * that report is completed, the report is marked "resolved" and its reporter
 * is notified that the asset is back in use.
 */
class AssetMaintenanceObserver
{
    public function created(AssetMaintenance $maintenance): void
    {
        $maintenance->asset?->syncStatusWithMaintenance();
    }

    public function updated(AssetMaintenance $maintenance): void
    {
        $maintenance->asset?->syncStatusWithMaintenance();

        // If the record was moved to a different asset, re-evaluate the one it left.
        if ($maintenance->wasChanged('asset_id') && $maintenance->getOriginal('asset_id')) {
            Asset::find($maintenance->getOriginal('asset_id'))?->syncStatusWithMaintenance();
        }

        if ($maintenance->wasChanged('status') && $maintenance->status === AssetMaintenance::STATUS_COMPLETED) {
            $this->resolveLinkedIssue($maintenance);
        }
    }

    public function deleted(AssetMaintenance $maintenance): void
    {
        $maintenance->asset?->syncStatusWithMaintenance();
    }

    private function resolveLinkedIssue(AssetMaintenance $maintenance): void
    {
        $report = $maintenance->issueReport;

        if (! $report || $report->status !== IssueReport::STATUS_ACCEPTED) {
            return;
        }

        // The maintenance completion is the reported activity; resolving the
        // linked report is an internal side effect.
        ActivityObserver::silently(fn () => $report->update([
            'status' => IssueReport::STATUS_RESOLVED,
        ]));

        $report->reporter?->notify(new IssueReportResolved($report->load('asset')));
    }
}
