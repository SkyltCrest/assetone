<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A picture attached to another record (asset, issue report, user).
 *
 * The bytes live in the `data` column, base64-encoded, so pictures survive
 * restarts on hosts with an ephemeral disk.
 */
class Photo extends Model
{
    protected $fillable = ['mime', 'size', 'data'];

    // Never drag the image bytes into JSON or activity notifications.
    protected $hidden = ['data'];

    public function photoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Address the browser loads the picture from. The timestamp makes a
     * replaced picture bypass the browser cache.
     */
    public function url(): string
    {
        return route('photos.show', ['photo' => $this->id, 'v' => $this->updated_at?->timestamp]);
    }
}
