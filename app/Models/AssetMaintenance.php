<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenance extends Model
{
    use HasFactory;

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
}
