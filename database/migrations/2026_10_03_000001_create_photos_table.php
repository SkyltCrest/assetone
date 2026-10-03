<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Uploaded pictures (asset photos, damage photos, profile pictures).
     *
     * The image bytes are kept in the database, base64-encoded, because the
     * production host has an ephemeral disk: anything written to storage/
     * disappears on the next restart.
     */
    public function up(): void
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->morphs('photoable');
            $table->string('mime', 50);
            $table->unsignedInteger('size');
            $table->longText('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
