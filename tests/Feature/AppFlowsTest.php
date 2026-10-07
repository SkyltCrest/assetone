<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetMaintenance;
use App\Models\AssetStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * End-to-end checks of the main pages and the flows that save data.
 * Runs on the seeded demo data (see DatabaseSeeder).
 */
class AppFlowsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function admin(): User
    {
        return User::where('role', 'administrator')->firstOrFail();
    }

    /** A 1x1 PNG, so the tests do not depend on the GD extension. */
    private function picture(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('photo.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));
    }

    private function assetData(array $overrides = []): array
    {
        $category = AssetCategory::with('types')->has('types')->firstOrFail();

        return $overrides + [
            'name' => 'Test Laptop',
            'serial_number' => 'SN-TEST-0001',
            'asset_category_id' => $category->id,
            'asset_type_id' => $category->types->first()->id,
            'asset_status_id' => AssetStatus::firstOrFail()->id,
            'purchase_date' => '2026-01-15',
            'purchase_price' => '1500.00',
            'department' => 'Finance Department',
            'asset_location_id' => AssetLocation::firstOrFail()->id,
            'custodian_id' => $this->admin()->id,
            'photo' => $this->picture(),
        ];
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/home')->assertRedirect('/login');
        $this->get('/login')->assertOk();
        $this->get('/forgot-password')->assertOk();
    }

    public function test_pages_are_not_kept_by_the_browser_so_back_after_logout_reaches_the_login_page(): void
    {
        $this->actingAs($this->admin());

        $cacheControl = $this->get('/home')->assertOk()->headers->get('Cache-Control');
        $this->assertStringContainsString('no-store', $cacheControl);

        $this->post('/logout')->assertRedirect();
        $this->get('/home')->assertRedirect('/login');
    }

    public function test_every_page_opens_for_an_administrator(): void
    {
        $this->actingAs($this->admin());

        $pages = [
            '/home', '/assets', '/assets/create', '/asset-management', '/assignments', '/maintenance',
            '/issue-verifications', '/my-assignments', '/issues', '/reports', '/notifications', '/users', '/settings',
            '/assets?sort=category', '/assignments?sort=pic&dir=desc', '/maintenance?sort=next&per_page=5',
        ];

        foreach ($pages as $page) {
            $this->get($page)->assertOk();
        }

        $asset = Asset::firstOrFail();
        $this->get("/assets/{$asset->id}")->assertOk();
        $this->get("/assets/{$asset->id}/edit")->assertOk();
    }

    public function test_kew_pa_forms_print_filled_in_and_staff_only_see_their_own(): void
    {
        $this->actingAs($this->admin());

        $loan = AssetAssignment::with('asset', 'custodian')->where('status', AssetAssignment::STATUS_ASSIGNED)->firstOrFail();
        $issue = \App\Models\IssueReport::with('asset', 'reporter')->firstOrFail();

        $this->get('/forms')->assertOk()->assertSee($loan->custodian->name);
        $this->get('/forms?form=pa10')->assertOk()->assertSee($issue->report_code);

        $loan->update(['place_of_use' => 'Bilik Mesyuarat Utama']);
        $loan->custodian->update(['position' => 'Pembantu Tadbir N19']);

        $this->get("/forms/print?form=pa9&only={$loan->id}")->assertOk()
            ->assertSee('KEW.PA-9')
            ->assertSee($loan->application_no)
            ->assertSee('Bilik Mesyuarat Utama')
            ->assertSee('Pembantu Tadbir N19')
            ->assertSee($loan->custodian->name)
            ->assertSee($loan->asset->asset_code)
            ->assertSee($loan->assigned_date->format('d/m/Y'));

        $this->get('/forms/print?form=pa10')->assertOk()
            ->assertSee('BORANG ADUAN KEROSAKAN ASET ALIH')
            ->assertSee($issue->asset->asset_code)
            ->assertSee($issue->description);

        // Someone else's records never reach a department staff member's forms.
        $staff = User::where('role', 'department_staff')->whereKeyNot($loan->custodian_id)->whereKeyNot($issue->reported_by)->firstOrFail();
        $this->actingAs($staff);

        $this->get("/forms/print?form=pa9&only={$loan->id}")->assertOk()->assertDontSee($loan->asset->asset_code);
        $this->get("/forms/print?form=pa10&only={$issue->id}")->assertOk()->assertDontSee($issue->asset->asset_code);
    }

    public function test_loans_by_one_person_on_one_day_share_an_application_number(): void
    {
        $this->actingAs($this->admin());

        $borrower = User::where('role', 'department_staff')->firstOrFail();
        $busy = AssetAssignment::whereIn('status', [AssetAssignment::STATUS_ASSIGNED, AssetAssignment::STATUS_PENDING])->pluck('asset_id');
        $free = Asset::whereNotIn('id', $busy)->take(2)->get();
        $this->assertCount(2, $free);

        foreach ($free as $asset) {
            $this->post('/assignments', [
                'asset_id' => $asset->id, 'custodian_id' => $borrower->id, 'assigned_date' => '2026-10-05',
                'loan_days' => 7, 'place_of_use' => 'Dewan Serbaguna',
            ])->assertSessionHasNoErrors();
        }

        $numbers = AssetAssignment::whereIn('asset_id', $free->pluck('id'))->whereDate('assigned_date', '2026-10-05')->pluck('application_no');
        $this->assertCount(2, $numbers);
        $this->assertCount(1, $numbers->unique());
        $this->assertMatchesRegularExpression('/^PA9-2026-\\d{4}$/', $numbers->first());

        // A loan on another day starts the next application.
        $this->assertNotSame($numbers->first(), AssetAssignment::applicationNoFor($borrower->id, '2026-10-06'));
    }

    public function test_exports_download_csv(): void
    {
        $this->actingAs($this->admin());

        foreach (['/assets?export=1', '/assignments?export=1', '/maintenance?export=1', '/users?export=1',
            '/asset-management?export=category', '/asset-management?export=location', '/asset-management?export=status',
            '/notifications/export'] as $url) {
            $response = $this->get($url)->assertOk();
            $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'), $url);
        }
    }

    public function test_a_failed_login_reports_the_attempts_left_and_then_locks(): void
    {
        $email = $this->admin()->email;

        $this->post('/login', ['email' => $email, 'password' => 'wrong'])
            ->assertSessionHasErrors(['password' => 'The password you entered is incorrect. 4 attempt(s) left.']);

        foreach (range(1, 4) as $i) {
            $response = $this->post('/login', ['email' => $email, 'password' => 'wrong']);
        }

        $response->assertSessionHas('lock_seconds');
        $this->assertGuest();
    }

    public function test_registering_an_asset_needs_a_location_and_a_person_in_charge(): void
    {
        $this->actingAs($this->admin());

        $this->post('/assets', $this->assetData(['asset_location_id' => '', 'custodian_id' => '']))
            ->assertSessionHasErrors(['asset_location_id', 'custodian_id']);

        $this->post('/assets', $this->assetData())
            ->assertRedirect('/assets/create')
            ->assertSessionHas('registered_asset');

        $asset = Asset::where('serial_number', 'SN-TEST-0001')->firstOrFail();
        $this->assertNotNull($asset->photo);
        $this->assertNotSame('', $asset->location_detail);
    }

    public function test_a_newly_registered_asset_is_always_active(): void
    {
        $this->actingAs($this->admin());
        $other = AssetStatus::where('name', '!=', Asset::STATUS_ACTIVE)->firstOrFail();

        $this->post('/assets', $this->assetData(['asset_status_id' => $other->id]))->assertSessionHasNoErrors();

        $asset = Asset::where('serial_number', 'SN-TEST-0001')->firstOrFail();
        $this->assertSame(Asset::STATUS_ACTIVE, $asset->assetStatus->name);
    }

    public function test_an_older_asset_can_be_updated_without_a_location_or_person_in_charge(): void
    {
        $this->actingAs($this->admin());
        $this->post('/assets', $this->assetData());
        $asset = Asset::where('serial_number', 'SN-TEST-0001')->firstOrFail();

        $data = $this->assetData(['name' => 'Renamed', 'asset_location_id' => '', 'custodian_id' => '']);
        unset($data['photo']);

        $this->put("/assets/{$asset->id}", $data)->assertSessionHasNoErrors();
        $this->assertSame('Renamed', $asset->fresh()->name);
    }

    public function test_a_reassignment_closes_the_previous_holder_only_when_it_is_accepted(): void
    {
        $admin = $this->admin();
        $other = User::where('id', '!=', $admin->id)->where('status', 'active')->firstOrFail();
        $this->actingAs($admin);
        $this->post('/assets', $this->assetData());
        $asset = Asset::where('serial_number', 'SN-TEST-0001')->firstOrFail();

        // First holder accepts.
        $this->post('/assignments', ['asset_id' => $asset->id, 'custodian_id' => $admin->id, 'assigned_date' => today()->toDateString(), 'loan_days' => 14]);
        $first = AssetAssignment::where('asset_id', $asset->id)->latest('id')->firstOrFail();
        $this->post("/my-assignments/{$first->id}/accept");
        $this->assertSame(AssetAssignment::STATUS_ASSIGNED, $first->fresh()->status);

        // Reassigned: the first record is untouched while the new one is pending.
        $this->post('/assignments', ['asset_id' => $asset->id, 'custodian_id' => $other->id, 'assigned_date' => today()->toDateString(), 'loan_days' => 7]);
        $second = AssetAssignment::where('asset_id', $asset->id)->latest('id')->firstOrFail();
        $this->assertSame(AssetAssignment::STATUS_ASSIGNED, $first->fresh()->status);
        $this->assertSame($admin->id, $asset->fresh()->custodian_id);

        // The new holder accepts: now the first record is closed.
        $this->actingAs($other)->post("/my-assignments/{$second->id}/accept");
        $this->assertSame(AssetAssignment::STATUS_UNASSIGNED, $first->fresh()->status);
        $this->assertSame($other->id, $asset->fresh()->custodian_id);
    }

    public function test_the_board_can_change_a_maintenance_status(): void
    {
        $this->actingAs($this->admin());
        $record = AssetMaintenance::firstOrFail();

        $this->patchJson("/maintenance/{$record->id}/status", ['status' => 'cancelled'])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame('cancelled', $record->fresh()->status);
        $this->patchJson("/maintenance/{$record->id}/status", ['status' => 'nonsense'])->assertStatus(422);
    }

    public function test_user_passwords_must_be_strong_and_a_person_in_charge_cannot_be_deleted(): void
    {
        $this->actingAs($this->admin());
        $user = ['name' => 'New Person', 'username' => 'newperson', 'email' => 'new@example.test', 'role' => 'department_staff', 'department' => 'Finance Department', 'status' => 'active'];

        $this->post('/users', $user + ['password' => 'weakpass'])->assertSessionHasErrors('password');
        $this->post('/users', $user + ['password' => 'Str0ng!Pass'])->assertSessionHasNoErrors();

        $created = User::where('username', 'newperson')->firstOrFail();
        $this->post("/users/{$created->id}/toggle");
        $this->assertSame('inactive', $created->fresh()->status);
        $this->post('/users/bulk-status', ['status' => 'active', 'ids' => [$created->id]]);
        $this->assertSame('active', $created->fresh()->status);

        // Someone who is in charge of an asset stays.
        $holder = User::has('assets')->where('id', '!=', $this->admin()->id)->first();
        if ($holder) {
            $this->delete("/users/{$holder->id}")->assertSessionHasErrors('user');
            $this->assertNotNull($holder->fresh());
        }
    }

    public function test_activity_notifications_are_filed_under_the_module_of_the_record(): void
    {
        $admin = $this->admin();

        Asset::firstOrFail()->update(['name' => 'Renamed Asset']);
        AssetMaintenance::firstOrFail()->update(['status' => 'cancelled']);
        AssetCategory::firstOrFail()->update(['name' => 'Renamed Category']);

        $modules = $admin->notifications()->get()
            ->filter(fn ($n) => ($n->data['type'] ?? null) === 'admin_activity')
            ->mapWithKeys(fn ($n) => [$n->data['title'] => $n->data['module']]);

        $this->assertSame('Registration', $modules->first(fn ($m, $title) => str_contains($title, 'Renamed Asset')));
        $this->assertSame('Maintenance', $modules->first(fn ($m, $title) => str_contains($title, 'Maintenance record')));
        $this->assertSame('Management', $modules->first(fn ($m, $title) => str_contains($title, 'Renamed Category')));
    }
}
