<?php

namespace App\Services;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Stores uploaded pictures in the database (see the photos migration for why).
 *
 * The browser already shrinks pictures before upload; when the GD extension
 * is available the server shrinks them again as a safety net.
 */
class PhotoService
{
    /** Longest side, in pixels, a stored picture may have. */
    private const MAX_SIDE = 1280;

    /** Validation rules for a single uploaded picture. */
    public const RULES = ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'];

    /**
     * Attach a new picture to the given record.
     */
    public function attach(Model $owner, UploadedFile $file, string $field = 'photo'): Photo
    {
        [$bytes, $mime] = $this->prepare($file);

        $encoded = base64_encode($bytes);
        $this->ensureFits($encoded, $field);

        $photo = new Photo(['mime' => $mime, 'size' => strlen($bytes), 'data' => $encoded]);
        $photo->photoable()->associate($owner);
        $photo->save();

        return $photo;
    }

    /**
     * Replace every picture on the record with a single new one.
     */
    public function replace(Model $owner, UploadedFile $file, string $field = 'photo'): Photo
    {
        return DB::transaction(function () use ($owner, $file, $field) {
            $photo = $this->attach($owner, $file, $field);
            Photo::where('photoable_type', $owner->getMorphClass())
                ->where('photoable_id', $owner->getKey())
                ->where('id', '!=', $photo->id)
                ->delete();

            return $photo;
        });
    }

    /**
     * @return array{0: string, 1: string}  [image bytes, mime type]
     */
    private function prepare(UploadedFile $file): array
    {
        $bytes = file_get_contents($file->getRealPath());
        $mime = $file->getMimeType() ?: 'image/jpeg';

        if (! function_exists('imagecreatefromstring')) {
            return [$bytes, $mime];
        }

        $image = @imagecreatefromstring($bytes);
        if ($image === false) {
            return [$bytes, $mime];
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(1, self::MAX_SIDE / max($width, $height));

        if ($scale < 1) {
            $resized = imagescale($image, (int) round($width * $scale), (int) round($height * $scale));
            if ($resized !== false) {
                $image = $resized;
            }
        }

        // Flatten onto white so transparent PNGs do not turn black as JPEG.
        $canvas = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopy($canvas, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

        ob_start();
        imagejpeg($canvas, null, 82);

        return [ob_get_clean(), 'image/jpeg'];
    }

    /**
     * Refuse a picture the database connection cannot take in one statement,
     * with a message the user can act on rather than a server error.
     */
    private function ensureFits(string $encoded, string $field): void
    {
        $limit = null;

        if (DB::connection()->getDriverName() === 'mysql') {
            $limit = (int) (DB::selectOne('SELECT @@max_allowed_packet AS size')->size ?? 0);
        }

        if ($limit && strlen($encoded) > $limit - 65536) {
            throw ValidationException::withMessages([
                $field => 'That picture is too large to store. Please choose a smaller one.',
            ]);
        }
    }
}
