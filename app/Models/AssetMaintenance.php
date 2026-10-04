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
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'maintenance_code',
        'asset_id',
        'type',
        'maintenance_date',
        'next_maintenance_date',
        'service_provider',
        'cost',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_date' => 'date',
            'next_maintenance_date' => 'date',
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
     * Where the next scheduled maintenance stands today.
     *
     * @return array{0: string, 1: string}  [label, Bootstrap colour]
     */
    public function dueStatus(): array
    {
        if ($this->next_maintenance_date === null) {
            return ['No Schedule', 'secondary'];
        }

        if (in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED], true)) {
            return ['Scheduled', 'success'];
        }

        $days = today()->diffInDays($this->next_maintenance_date, false);

        return match (true) {
            $days < 0 => ['Overdue', 'danger'],
            $days <= 7 => ['Due Soon', 'warning'],
            default => ['Scheduled', 'success'],
        };
    }

    /**
     * How far away the next maintenance date is, in words ("In 5 days", "3 days overdue").
     */
    public function dueRelative(): string
    {
        if ($this->next_maintenance_date === null) {
            return '';
        }

        $days = (int) today()->diffInDays($this->next_maintenance_date, false);

        return match (true) {
            $days < 0 => abs($days).' day'.(abs($days) === 1 ? '' : 's').' overdue',
            $days === 0 => 'Due today',
            $days === 1 => 'Tomorrow',
            default => 'In '.$days.' days',
        };
    }

    /**
     * Open records whose next maintenance date has passed.
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED])
            ->whereNotNull('next_maintenance_date')
            ->whereDate('next_maintenance_date', '<', today());
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
