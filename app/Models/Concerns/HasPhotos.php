<?php

namespace App\Models\Concerns;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Gives a model attached pictures.
 *
 * Both relations leave out the heavy `data` column, so listing records with
 * their pictures never loads image bytes; those are only read when a picture
 * is actually served (PhotoController).
 */
trait HasPhotos
{
    private const PHOTO_COLUMNS = ['photos.id', 'photos.photoable_id', 'photos.photoable_type', 'photos.mime', 'photos.updated_at'];

    public static function bootHasPhotos(): void
    {
        static::deleted(function ($model) {
            Photo::where('photoable_type', $model->getMorphClass())
                ->where('photoable_id', $model->getKey())
                ->delete();
        });
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable')->select(self::PHOTO_COLUMNS)->orderBy('photos.id');
    }

    public function photo(): MorphOne
    {
        return $this->morphOne(Photo::class, 'photoable')->select(self::PHOTO_COLUMNS);
    }

    public function photoUrl(): ?string
    {
        return $this->photo?->url();
    }
}
