<?php

namespace App\Observers;

use App\Models\Asset;
use App\Models\AssetMaintenance;

/**
 * Keeps an asset's own status ("Active" / "Under Maintenance") in step with
 * its maintenance records, so the status never has to be changed by hand on
 * top of logging the maintenance work.
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
    }

    public function deleted(AssetMaintenance $maintenance): void
    {
        $maintenance->asset?->syncStatusWithMaintenance();
    }
}
