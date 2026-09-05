<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetMaintenance;
use App\Models\AssetStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed demo data. All seeded users share the password "password".
     */
    public function run(): void
    {
        // Users (one per role)
        $admin = User::updateOrCreate(['email' => 'admin@mdpt.gov.my'], [
            'name' => 'System Administrator',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'administrator',
            'status' => 'active',
        ]);

        $ahmad = User::updateOrCreate(['email' => 'ahmad@mdpt.gov.my'], [
            'name' => 'Ahmad bin Ali',
            'username' => 'ahmad',
            'password' => Hash::make('password'),
            'role' => 'asset_officer',
            'status' => 'active',
        ]);

        $siti = User::updateOrCreate(['email' => 'siti@mdpt.gov.my'], [
            'name' => 'Siti Aminah',
            'username' => 'siti',
            'password' => Hash::make('password'),
            'role' => 'department_staff',
            'status' => 'active',
        ]);

        $faiz = User::updateOrCreate(['email' => 'faiz@mdpt.gov.my'], [
            'name' => 'Muhammad Faiz',
            'username' => 'faiz',
            'password' => Hash::make('password'),
            'role' => 'management',
            'status' => 'active',
        ]);

        User::updateOrCreate(['email' => 'aisyah@mdpt.gov.my'], [
            'name' => 'Nur Aisyah',
            'username' => 'aisyah',
            'password' => Hash::make('password'),
            'role' => 'department_staff',
            'status' => 'inactive',
        ]);

        // Asset categories
        $categories = [
            'CAT-001' => AssetCategory::updateOrCreate(['code' => 'CAT-001'], ['name' => 'Computer & IT Equipment', 'description' => 'Computers and IT-related equipment', 'status' => 'active']),
            'CAT-002' => AssetCategory::updateOrCreate(['code' => 'CAT-002'], ['name' => 'Office Equipment', 'description' => 'Equipment used for office operations', 'status' => 'active']),
            'CAT-003' => AssetCategory::updateOrCreate(['code' => 'CAT-003'], ['name' => 'Furniture', 'description' => 'Tables, chairs and office furniture', 'status' => 'active']),
            'CAT-004' => AssetCategory::updateOrCreate(['code' => 'CAT-004'], ['name' => 'Vehicle', 'description' => 'Official vehicles owned by MDPT', 'status' => 'active']),
        ];

        // Asset locations
        $locations = [
            'LOC-001' => AssetLocation::updateOrCreate(['code' => 'LOC-001'], ['name' => 'IT Department', 'department' => 'Information Technology', 'description' => 'Main IT department office', 'status' => 'active']),
            'LOC-002' => AssetLocation::updateOrCreate(['code' => 'LOC-002'], ['name' => 'Finance Department', 'department' => 'Finance', 'description' => 'Finance department office', 'status' => 'active']),
            'LOC-003' => AssetLocation::updateOrCreate(['code' => 'LOC-003'], ['name' => 'Administration Office', 'department' => 'Administration', 'description' => 'Main administration office', 'status' => 'active']),
            'LOC-004' => AssetLocation::updateOrCreate(['code' => 'LOC-004'], ['name' => 'Meeting Room', 'department' => 'Management', 'description' => 'Level 3 main meeting room', 'status' => 'active']),
        ];

        // Asset statuses
        $statuses = [
            'Active' => AssetStatus::updateOrCreate(['code' => 'STAT-001'], ['name' => 'Active', 'description' => 'Asset is currently available and in use.', 'badge_color' => 'success', 'status' => 'active']),
            'Under Maintenance' => AssetStatus::updateOrCreate(['code' => 'STAT-002'], ['name' => 'Under Maintenance', 'description' => 'Asset is currently being repaired or maintained.', 'badge_color' => 'warning', 'status' => 'active']),
            'Unavailable' => AssetStatus::updateOrCreate(['code' => 'STAT-003'], ['name' => 'Unavailable', 'description' => 'Asset is not available for use.', 'badge_color' => 'danger', 'status' => 'active']),
            'Disposed' => AssetStatus::updateOrCreate(['code' => 'STAT-004'], ['name' => 'Disposed', 'description' => 'Asset has been disposed of or removed.', 'badge_color' => 'secondary', 'status' => 'active']),
            'Lost' => AssetStatus::updateOrCreate(['code' => 'STAT-005'], ['name' => 'Lost', 'description' => 'Asset has been reported as lost.', 'badge_color' => 'dark', 'status' => 'active']),
        ];

        // Assets
        $assetData = [
            ['code' => 'AST-001', 'name' => 'Dell Latitude 5420', 'category' => 'CAT-001', 'location' => 'LOC-001', 'department' => 'Information Technology', 'detail' => 'Level 2, IT Department', 'status' => 'Active', 'custodian' => $ahmad, 'price' => 3800.00, 'supplier' => 'Dell Malaysia'],
            ['code' => 'AST-002', 'name' => 'HP LaserJet Pro', 'category' => 'CAT-002', 'location' => 'LOC-002', 'department' => 'Finance', 'detail' => 'Level 1, Finance Department', 'status' => 'Under Maintenance', 'custodian' => $siti, 'price' => 1200.00, 'supplier' => 'HP Malaysia'],
            ['code' => 'AST-003', 'name' => 'Office Projector', 'category' => 'CAT-002', 'location' => 'LOC-004', 'department' => 'Management', 'detail' => 'Level 3, Meeting Room', 'status' => 'Active', 'custodian' => $faiz, 'price' => 2500.00, 'supplier' => 'Epson Malaysia'],
            ['code' => 'AST-004', 'name' => 'Dell Desktop PC', 'category' => 'CAT-001', 'location' => 'LOC-003', 'department' => 'Administration', 'detail' => 'Level 1, Administration Office', 'status' => 'Unavailable', 'custodian' => null, 'price' => 2900.00, 'supplier' => 'Dell Malaysia'],
            ['code' => 'AST-005', 'name' => 'Office Chair (Ergonomic)', 'category' => 'CAT-003', 'location' => 'LOC-003', 'department' => 'Administration', 'detail' => 'Level 1, Administration Office', 'status' => 'Active', 'custodian' => null, 'price' => 450.00, 'supplier' => 'Office Furniture Sdn Bhd'],
            ['code' => 'AST-006', 'name' => 'Toyota Hilux (Official Vehicle)', 'category' => 'CAT-004', 'location' => 'LOC-003', 'department' => 'Administration', 'detail' => 'MDPT Vehicle Bay', 'status' => 'Active', 'custodian' => null, 'price' => 98000.00, 'supplier' => 'UMW Toyota'],
        ];

        $assets = [];
        foreach ($assetData as $row) {
            $assets[$row['code']] = Asset::updateOrCreate(['asset_code' => $row['code']], [
                'name' => $row['name'],
                'description' => null,
                'asset_category_id' => $categories[$row['category']]->id,
                'asset_location_id' => $locations[$row['location']]->id,
                'asset_status_id' => $statuses[$row['status']]->id,
                'custodian_id' => $row['custodian']?->id,
                'department' => $row['department'],
                'location_detail' => $row['detail'],
                'purchase_date' => now()->subMonths(rand(2, 24))->toDateString(),
                'purchase_price' => $row['price'],
                'supplier' => $row['supplier'],
                'warranty_expiry_date' => now()->addMonths(rand(6, 24))->toDateString(),
            ]);
        }

        // Assignment history
        $assignmentData = [
            ['asset' => 'AST-001', 'custodian' => $ahmad, 'department' => 'Information Technology', 'date' => '2026-07-20', 'status' => 'assigned', 'verified' => true],
            ['asset' => 'AST-002', 'custodian' => $siti, 'department' => 'Finance', 'date' => '2026-07-18', 'status' => 'assigned', 'verified' => true],
            ['asset' => 'AST-003', 'custodian' => $faiz, 'department' => 'Management', 'date' => '2026-07-15', 'status' => 'assigned', 'verified' => true],
            // Awaiting Siti's verification
            ['asset' => 'AST-005', 'custodian' => $siti, 'department' => 'Administration', 'date' => '2026-09-01', 'status' => 'pending_verification', 'verified' => false],
        ];

        foreach ($assignmentData as $row) {
            $assignment = AssetAssignment::updateOrCreate([
                'asset_id' => $assets[$row['asset']]->id,
                'assigned_date' => $row['date'],
            ], [
                'custodian_id' => $row['custodian']->id,
                'assigned_by' => $ahmad->id,
                'department' => $row['department'],
                'status' => $row['status'],
                'verified_at' => $row['verified'] ? $row['date'].' 09:00:00' : null,
                'notes' => null,
            ]);

            if ($row['status'] === 'pending_verification' && $assignment->wasRecentlyCreated) {
                $row['custodian']->notify(new \App\Notifications\AssignmentAwaitingVerification(
                    $assignment->load('asset', 'assignedBy')
                ));
            }
        }

        // Maintenance history
        $maintenanceData = [
            ['code' => 'MNT-001', 'asset' => 'AST-002', 'type' => 'repair', 'date' => '2026-07-20', 'provider' => 'HP Service Center', 'cost' => 250.00, 'status' => 'in_progress', 'desc' => 'Paper jam and toner replacement.'],
            ['code' => 'MNT-002', 'asset' => 'AST-004', 'type' => 'service', 'date' => '2026-07-18', 'provider' => 'Dell Service Center', 'cost' => 180.00, 'status' => 'completed', 'desc' => 'Preventive maintenance and diagnostics.'],
            ['code' => 'MNT-003', 'asset' => 'AST-001', 'type' => 'inspection', 'date' => '2026-06-30', 'provider' => 'Internal IT Team', 'cost' => null, 'status' => 'completed', 'desc' => 'Routine inspection, no issues found.'],
        ];

        foreach ($maintenanceData as $row) {
            AssetMaintenance::updateOrCreate(['maintenance_code' => $row['code']], [
                'asset_id' => $assets[$row['asset']]->id,
                'type' => $row['type'],
                'maintenance_date' => $row['date'],
                'service_provider' => $row['provider'],
                'cost' => $row['cost'],
                'status' => $row['status'],
                'description' => $row['desc'],
            ]);
        }
    }
}
