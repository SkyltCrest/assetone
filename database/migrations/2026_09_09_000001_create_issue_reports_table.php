<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issue_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_code')->unique();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description');
            $table->enum('status', ['pending_verification', 'accepted', 'rejected', 'resolved'])
                ->default('pending_verification');
            $table->foreignId('verified_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('resolution_note')->nullable();
            $table->foreignId('asset_maintenance_id')->nullable()
                ->constrained('asset_maintenances')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_reports');
    }
};
