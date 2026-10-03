<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('asset_type_id')->nullable()->after('asset_category_id')
                ->constrained('asset_types')->nullOnDelete();
            $table->string('serial_number')->nullable()->unique()->after('name');
            $table->string('po_reference')->nullable()->after('supplier');
            $table->date('assigned_date')->nullable()->after('custodian_id');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('asset_type_id');
            $table->dropUnique(['serial_number']);
            $table->dropColumn(['serial_number', 'po_reference', 'assigned_date']);
        });
    }
};
