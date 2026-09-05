<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Widen the status enum to cover the verification workflow.
        DB::statement("ALTER TABLE asset_assignments MODIFY COLUMN status
            ENUM('pending_verification', 'assigned', 'rejected', 'unassigned')
            NOT NULL DEFAULT 'pending_verification'");

        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->foreignId('assigned_by')->nullable()->after('custodian_id')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('status');
            $table->text('rejection_reason')->nullable()->after('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_by');
            $table->dropColumn(['verified_at', 'rejection_reason']);
        });

        DB::statement("ALTER TABLE asset_assignments MODIFY COLUMN status
            ENUM('assigned', 'unassigned') NOT NULL DEFAULT 'assigned'");
    }
};
