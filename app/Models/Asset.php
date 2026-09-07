<?php

namespace App\Models;

use App\Observers\ActivityObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    /**
     * Asset status names that are driven automatically by maintenance activity.
     * These match the names created by the database seeder.
     */
    public const STATUS_ACTIVE = 'Active';
    public const STATUS_UNDER_MAINTENANCE = 'Under Maintenance';

    protected $fillable = [
        'asset_code',
        'name',
        'description',
        'asset_category_id',
        'asset_location_id',
        'asset_status_id',
        'custodian_id',
        'department',
        'location_detail',
        'purchase_date',
        'purchase_price',
        'supplier',
        'warranty_expiry_date',
        'qr_code_path',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'warranty_expiry_date' => 'date',
            'purchase_price' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(AssetLocation::class, 'asset_location_id');
    }

    public function assetStatus(): BelongsTo
    {
        return $this->belongsTo(AssetStatus::class, 'asset_status_id');
    }

    public function custodian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'custodian_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    /**
     * Keep the asset's own status in step with its maintenance records.
     *
     * While at least one maintenance record is "in progress" the asset is
     * flipped to "Under Maintenance"; once none remain it is returned to
     * "Active". Any other status (Unavailable, Disposed, Lost, or a custom
     * one) is left untouched so a manual decision is never overridden.
     */
    public function syncStatusWithMaintenance(): void
    {
        $underMaintenance = AssetStatus::firstWhere('name', self::STATUS_UNDER_MAINTENANCE);
        $active = AssetStatus::firstWhere('name', self::STATUS_ACTIVE);

        if (! $underMaintenance || ! $active) {
            return;
        }

        $hasOpenMaintenance = $this->maintenances()
            ->where('status', AssetMaintenance::STATUS_IN_PROGRESS)
            ->exists();

        $targetId = match (true) {
            $hasOpenMaintenance && (int) $this->asset_status_id === $active->id => $underMaintenance->id,
            ! $hasOpenMaintenance && (int) $this->asset_status_id === $underMaintenance->id => $active->id,
            default => null,
        };

        if ($targetId === null) {
            return;
        }

        // The maintenance record is the reported activity; this status flip is
        // an internal side effect, so it is not announced to administrators.
        ActivityObserver::silently(fn () => $this->forceFill(['asset_status_id' => $targetId])->save());
    }

    /**
     * Search by asset code or name.
     */
    public function scopeSearch($query, ?string $term)
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('asset_code', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%");
        });
    }
}
