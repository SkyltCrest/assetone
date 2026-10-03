<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A kind of asset within a category (e.g. "Laptop" under "Computer & IT
 * Equipment"). Its code forms the middle part of generated asset codes.
 */
class AssetType extends Model
{
    protected $fillable = ['asset_category_id', 'name', 'code'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
