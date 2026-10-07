<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Activity notifications about assets and maintenance records were filed under
 * "Management" (the fallback module) because the observer could not recognise
 * those two models. Refile the ones already stored, going by their title.
 */
return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('notifications')
            ->where('data', 'like', '%admin_activity%')
            ->orderBy('created_at')
            ->get(['id', 'data']);

        foreach ($rows as $row) {
            $data = json_decode($row->data, true);

            if (! is_array($data) || ($data['type'] ?? null) !== 'admin_activity' || ($data['module'] ?? null) !== 'Management') {
                continue;
            }

            // Titles read "<Action> — <Subject>"; categories, locations and
            // statuses ("Asset category ...") really do belong to Management.
            $module = match (1) {
                preg_match('/^[^—]+ — Asset "/u', $data['title'] ?? '') => 'Registration',
                preg_match('/^[^—]+ — Maintenance record /u', $data['title'] ?? '') => 'Maintenance',
                default => null,
            };

            if ($module) {
                $data['module'] = $module;

                DB::table('notifications')->where('id', $row->id)->update(['data' => json_encode($data)]);
            }
        }
    }

    public function down(): void
    {
        // The earlier labels were wrong; there is nothing worth restoring.
    }
};
