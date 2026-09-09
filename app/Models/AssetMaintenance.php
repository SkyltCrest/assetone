<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AssetMaintenance extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'maintenance_code',
        'asset_id',
        'type',
        'maintenance_date',
        'service_provider',
        'cost',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_date' => 'date',
            'cost' => 'decimal:2',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * The reported issue this maintenance record was opened for, if any.
     */
    public function issueReport(): HasOne
    {
        return $this->hasOne(IssueReport::class);
    }

    /**
     * Next sequential maintenance code, e.g. MNT-004.
     */
    public static function nextCode(): string
    {
        $last = static::orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->maintenance_code, 4)) + 1 : 1;

        return 'MNT-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
