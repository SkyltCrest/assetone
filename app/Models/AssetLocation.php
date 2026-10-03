<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'department',
        'building',
        'floor',
        'room',
        'description',
        'status',
    ];

    /**
     * "Block A / Level 2 / Room 2.05" - only the parts that are filled in.
     */
    public function place(): string
    {
        return implode(' / ', array_filter([$this->building, $this->floor, $this->room]));
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
