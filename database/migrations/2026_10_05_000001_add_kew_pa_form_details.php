<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Details the KEW.PA-9 / KEW.PA-10 forms ask for that were not recorded yet:
 * a person's post (Jawatan), where a loaned asset is used (Tempat Digunakan)
 * and the loan application number (No. Permohonan).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('position')->nullable()->after('department');
        });

        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->string('application_no')->nullable()->index()->after('id');
            $table->string('place_of_use')->nullable()->after('department');
        });

        // Existing loans: one number per borrower and loan date, counted per year.
        $numbers = [];
        $sequence = [];

        foreach (DB::table('asset_assignments')->orderBy('assigned_date')->orderBy('id')->get(['id', 'custodian_id', 'assigned_date']) as $row) {
            $year = substr((string) $row->assigned_date, 0, 4);
            $loan = $row->custodian_id.'|'.substr((string) $row->assigned_date, 0, 10);

            $numbers[$loan] ??= sprintf('PA9-%s-%04d', $year, $sequence[$year] = ($sequence[$year] ?? 0) + 1);

            DB::table('asset_assignments')->where('id', $row->id)->update(['application_no' => $numbers[$loan]]);
        }
    }

    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropIndex(['application_no']);
            $table->dropColumn(['application_no', 'place_of_use']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
