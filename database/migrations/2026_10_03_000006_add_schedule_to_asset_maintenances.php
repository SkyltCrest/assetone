<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE asset_maintenances MODIFY COLUMN status
            ENUM('pending', 'in_progress', 'completed', 'cancelled')
            NOT NULL DEFAULT 'pending'");

        Schema::table('asset_maintenances', function (Blueprint $table) {
            $table->date('next_maintenance_date')->nullable()->after('maintenance_date');
        });
    }

    public function down(): void
    {
        Schema::table('asset_maintenances', function (Blueprint $table) {
            $table->dropColumn('next_maintenance_date');
        });

        DB::table('asset_maintenances')->where('status', 'cancelled')->update(['status' => 'completed']);

        DB::statement("ALTER TABLE asset_maintenances MODIFY COLUMN status
            ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending'");
    }
};
