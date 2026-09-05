<?php

namespace App\Support;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetMaintenance;
use App\Models\AssetStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Turns a model into a human-readable subject and a link, for the
 * activity notifications administrators receive.
 */
class ActivityDescriptor
{
    /**
     * A noun phrase describing the record, e.g. Asset "AST-001 — Dell Latitude".
     */
    public static function subject(Model $model): string
    {
        return match (true) {
            $model instanceof Asset => 'asset "'.trim(($model->asset_code ? $model->asset_code.' — ' : '').$model->name).'"',
            $model instanceof AssetAssignment => 'the assignment of '
                .($model->asset->asset_code ?? 'an asset')
                .' to '.($model->custodian->name ?? 'a staff member'),
            $model instanceof AssetMaintenance => 'maintenance record '.($model->maintenance_code ?? '#'.$model->id),
            $model instanceof AssetCategory => 'asset category "'.$model->name.'"',
            $model instanceof AssetLocation => 'asset location "'.$model->name.'"',
            $model instanceof AssetStatus => 'asset status "'.$model->name.'"',
            $model instanceof User => 'user account "'.$model->name.'"',
            default => class_basename($model),
        };
    }

    /**
     * Where an administrator should land when they open the notification.
     * Deleted records point at the relevant list rather than a dead detail page.
     */
    public static function url(Model $model, bool $deleted = false): ?string
    {
        return match (true) {
            $model instanceof Asset => $deleted ? route('assets.index') : route('assets.show', $model->id),
            $model instanceof AssetAssignment => route('assignments.index'),
            $model instanceof AssetMaintenance => route('maintenance.index'),
            $model instanceof AssetCategory,
            $model instanceof AssetLocation,
            $model instanceof AssetStatus => route('asset-management.index'),
            $model instanceof User => route('users.index'),
            default => null,
        };
    }
}
