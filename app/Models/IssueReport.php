<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueReport extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending_verification';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = [
        'report_code',
        'asset_id',
        'reported_by',
        'description',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'resolution_note',
        'asset_maintenance_id',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(AssetMaintenance::class, 'asset_maintenance_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Next sequential report code, e.g. ISS-001.
     */
    public static function nextCode(): string
    {
        $last = static::orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->report_code, 4)) + 1 : 1;

        return 'ISS-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Human-readable label for the current status.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending Verification',
            self::STATUS_ACCEPTED => 'Accepted — Under Maintenance',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_RESOLVED => 'Resolved',
            default => ucfirst($this->status),
        };
    }

    /**
     * Bootstrap contextual colour for the current status.
     */
    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_ACCEPTED => 'info',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_RESOLVED => 'success',
            default => 'secondary',
        };
    }
}
