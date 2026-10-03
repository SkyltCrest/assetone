<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAssignment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending_verification';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_UNASSIGNED = 'unassigned';

    protected $fillable = [
        'asset_id',
        'custodian_id',
        'assigned_by',
        'department',
        'assigned_date',
        'loan_days',
        'due_date',
        'returned_date',
        'status',
        'verified_at',
        'rejection_reason',
        'notes',
        'return_note',
    ];

    protected function casts(): array
    {
        return [
            'assigned_date' => 'date',
            'due_date' => 'date',
            'returned_date' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function custodian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'custodian_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * An accepted assignment that is still out past its due date.
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_ASSIGNED
            && $this->due_date !== null
            && $this->due_date->isBefore(today());
    }

    /**
     * Assignments that are out past their due date.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_ASSIGNED)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today());
    }

    /**
     * Human-readable label for the current status.
     */
    public function statusLabel(): string
    {
        if ($this->isOverdue()) {
            return 'Overdue';
        }

        return match ($this->status) {
            self::STATUS_PENDING => 'Pending Verification',
            self::STATUS_ASSIGNED => 'Assigned',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_UNASSIGNED => 'Returned',
            default => ucfirst($this->status),
        };
    }

    /**
     * Bootstrap contextual colour for the current status.
     */
    public function statusColor(): string
    {
        if ($this->isOverdue()) {
            return 'danger';
        }

        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_ASSIGNED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_UNASSIGNED => 'secondary',
            default => 'secondary',
        };
    }
}
