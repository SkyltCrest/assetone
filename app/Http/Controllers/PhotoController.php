<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use Illuminate\Http\Response;

class PhotoController extends Controller
{
    /**
     * Serve a stored picture. Signed-in users only (route middleware).
     */
    public function show(Photo $photo): Response
    {
        return response(base64_decode($photo->data))
            ->header('Content-Type', $photo->mime)
            ->header('Cache-Control', 'private, max-age=604800');
    }
}
