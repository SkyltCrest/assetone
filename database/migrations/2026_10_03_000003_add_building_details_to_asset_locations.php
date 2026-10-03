<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_locations', function (Blueprint $table) {
            $table->string('building')->nullable()->after('department');
            $table->string('floor')->nullable()->after('building');
            $table->string('room')->nullable()->after('floor');
        });
    }

    public function down(): void
    {
        Schema::table('asset_locations', function (Blueprint $table) {
            $table->dropColumn(['building', 'floor', 'room']);
        });
    }
};
