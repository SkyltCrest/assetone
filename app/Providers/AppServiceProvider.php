<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetMaintenance;
use App\Models\AssetStatus;
use App\Models\User;
use App\Observers\ActivityObserver;
use App\Observers\AssetMaintenanceObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Domain models whose every create/update/delete is reported to administrators.
     *
     * @var array<int, class-string<\Illuminate\Database\Eloquent\Model>>
     */
    private const ACTIVITY_MODELS = [
        Asset::class,
        AssetAssignment::class,
        AssetMaintenance::class,
        AssetCategory::class,
        AssetLocation::class,
        AssetStatus::class,
        User::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Boot services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        foreach (self::ACTIVITY_MODELS as $model) {
            $model::observe(ActivityObserver::class);
        }

        AssetMaintenance::observe(AssetMaintenanceObserver::class);
    }
}
