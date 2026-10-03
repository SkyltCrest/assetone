<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->unsignedSmallInteger('loan_days')->nullable()->after('assigned_date');
            $table->date('due_date')->nullable()->after('loan_days');
            $table->date('returned_date')->nullable()->after('due_date');
            $table->text('return_note')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropColumn(['loan_days', 'due_date', 'returned_date', 'return_note']);
        });
    }
};
