<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\ConsultationService;
use App\Models\Office;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminModuleTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_user_management(): void
    {
        User::factory()->create(['role' => 'registrar']);

        $this->actingAs($this->makeAdmin())
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('User Management')
            ->assertSee('registrar');
    }

    public function test_user_management_shows_registration_type_for_public_accounts(): void
    {
        $admin = $this->makeAdmin();

        User::factory()->create(['role' => 'student', 'name' => 'Alumni Person', 'registration_type' => 'alumni']);
        User::factory()->create(['role' => 'student', 'name' => 'Parent Person', 'registration_type' => 'parent']);
        User::factory()->create(['role' => 'student', 'name' => 'Guest Person', 'registration_type' => 'guest']);
        User::factory()->create(['role' => 'student', 'name' => 'Student Person', 'registration_type' => null]);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('data-role-type="alumni"', false)
            ->assertSee('data-role-type="parent"', false)
            ->assertSee('data-role-type="guest"', false)
            ->assertSee('data-role-type="student"', false);
    }

    public function test_user_management_role_filter_matches_registration_type(): void
    {
        $admin = $this->makeAdmin();

        User::factory()->create(['role' => 'student', 'name' => 'Alumni Person', 'registration_type' => 'alumni']);
        User::factory()->create(['role' => 'student', 'name' => 'Parent Person', 'registration_type' => 'parent']);
        User::factory()->create(['role' => 'student', 'name' => 'Student Person', 'registration_type' => null]);

        $this->actingAs($admin)
            ->get('/admin/users?role=alumni')
            ->assertOk()
            ->assertSee('Alumni Person')
            ->assertDontSee('Parent Person')
            ->assertDontSee('Student Person');

        $this->actingAs($admin)
            ->get('/admin/users?role=student')
            ->assertOk()
            ->assertSee('Student Person')
            ->assertDontSee('Alumni Person')
            ->assertDontSee('Parent Person');
    }

    public function test_user_management_counts_and_filters_every_office_account(): void
    {
        $admin = $this->makeAdmin();

        User::factory()->create(['role' => 'department', 'office' => 'CICI']);
        User::factory()->create(['role' => 'office', 'office' => 'Registrar']);
        User::factory()->create(['role' => 'osas', 'office' => 'OSAS']); // role outside the catalog
        User::factory()->create(['role' => 'guard', 'office' => 'Main Gate']);

        $html = $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->getContent();

        // admin + department + office + osas + guard — every non-public account.
        $this->assertSame(
            1,
            preg_match('/data-stat="office"[^>]*>\s*5\s*</', $html),
            'Every office, department and security account should be counted.'
        );

        foreach (['department', 'office', 'guard', 'osas'] as $role) {
            $this->assertStringContainsString(
                'value="'.$role.'"',
                $html,
                "The {$role} role should be offered by the role filter."
            );
        }
    }

    public function test_user_management_counts_every_public_account_type(): void
    {
        $admin = $this->makeAdmin();

        User::factory()->create(['role' => 'student', 'registration_type' => 'alumni']);
        User::factory()->create(['role' => 'student', 'registration_type' => 'alumni']);
        User::factory()->create(['role' => 'student', 'registration_type' => 'parent']);
        User::factory()->create(['role' => 'student', 'registration_type' => 'guest']);
        User::factory()->create(['role' => 'student', 'registration_type' => null]);

        $html = $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->getContent();

        foreach ([
            'total' => 6,
            'active' => 6,
            'students' => 1,
            'alumni' => 2,
            'parents' => 1,
            'guests' => 1,
        ] as $stat => $expected) {
            $matched = preg_match("/data-stat=\"{$stat}\"[^>]*>\\s*{$expected}\\s*</", $html);

            $this->assertSame(1, $matched, "The {$stat} card should read {$expected}.");
        }
    }

    public function test_admin_can_create_and_deactivate_user(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'New Registrar',
                'email' => 'newregistrar@isufst.edu.ph',
                'role' => 'registrar',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $user = User::where('email', 'newregistrar@isufst.edu.ph')->first();
        $this->assertNotNull($user);

        $this->actingAs($admin)
            ->put("/admin/users/{$user->id}/toggle")
            ->assertRedirect();

        $this->assertFalse($user->fresh()->is_active);

        $this->assertDatabaseHas('audit_logs', ['action' => 'user.created']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.deactivated']);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->delete("/admin/users/{$admin->id}")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNotNull($admin->fresh());
    }

    public function test_admin_can_reset_a_users_password(): void
    {
        $admin = $this->makeAdmin();
        $user = User::factory()->create(['role' => 'student', 'password' => 'password123']);

        $this->actingAs($admin)
            ->put("/admin/users/{$user->id}/password", [
                'password' => 'BrandNewPass42',
                'password_confirmation' => 'BrandNewPass42',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $user->refresh();

        $this->assertTrue(Hash::check('BrandNewPass42', $user->password));
        $this->assertFalse(Hash::check('password123', $user->password));

        $this->assertDatabaseHas('audit_logs', ['action' => 'user.password_reset']);
    }

    public function test_password_reset_rejects_short_and_mismatched_passwords(): void
    {
        $admin = $this->makeAdmin();
        $user = User::factory()->create(['role' => 'student', 'password' => 'password123']);

        $this->actingAs($admin)
            ->put("/admin/users/{$user->id}/password", [
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrors('password');

        $this->actingAs($admin)
            ->put("/admin/users/{$user->id}/password", [
                'password' => 'BrandNewPass42',
                'password_confirmation' => 'DifferentPass42',
            ])
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password123', $user->fresh()->password));
        $this->assertDatabaseMissing('audit_logs', ['action' => 'user.password_reset']);
    }

    public function test_student_cannot_reset_a_password(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $victim = User::factory()->create(['role' => 'student', 'password' => 'password123']);

        $this->actingAs($student)
            ->put("/admin/users/{$victim->id}/password", [
                'password' => 'BrandNewPass42',
                'password_confirmation' => 'BrandNewPass42',
            ])
            ->assertForbidden();

        $this->assertTrue(Hash::check('password123', $victim->fresh()->password));
        $this->assertDatabaseMissing('audit_logs', ['action' => 'user.password_reset']);
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'role' => 'registrar',
            'password' => 'password123',
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_manage_departments(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post('/admin/offices', [
                'name' => 'Office of the Registrar',
                'location' => 'Main Building, Ground Floor',
                'contact' => '(033) 555-1234',
                'hours' => 'Mon - Fri, 8 AM - 5 PM',
                'description' => 'Student records and document issuance.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $office = Office::where('name', 'Office of the Registrar')->first();
        $this->assertNotNull($office);

        $this->actingAs($admin)
            ->put("/admin/offices/{$office->id}", [
                'name' => 'Office of the Registrar',
                'location' => 'Main Building, 1st Floor',
                'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertEquals('Main Building, 1st Floor', $office->fresh()->location);

        $this->actingAs($admin)
            ->get('/admin/offices')
            ->assertOk()
            ->assertSee('Department Settings');

        $this->assertDatabaseHas('audit_logs', ['action' => 'office.created']);
    }

    /**
     * The office's login is created in one place - the Add Office form on
     * User Management - which also registers the office the first time it is
     * named there. The account lands approved and verified, so the desk can
     * sign in immediately instead of waiting in the approval queue, and the
     * office's own card shows who can sign in for it.
     */
    public function test_add_office_provisions_the_office_and_its_login(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'Maria Santos',
                'email' => 'property@isufst.edu.ph',
                'role' => 'office',
                'office' => 'Property Management Office',
                'position' => 'Property Custodian',
                'contact_number' => '09171234567',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $account = User::where('email', 'property@isufst.edu.ph')->firstOrFail();

        $this->assertSame('office', $account->role);
        $this->assertSame('Property Management Office', $account->office);
        $this->assertSame('Property Custodian', $account->position);
        $this->assertTrue($account->is_active);
        $this->assertTrue($account->isApproved());
        $this->assertNotNull($account->email_verified_at);

        // Naming a new office signs it into the directory too.
        $this->assertDatabaseHas('offices', ['name' => 'Property Management Office']);

        // The office card tells the administrator which desk can sign in.
        $this->actingAs($admin)
            ->get('/admin/offices')
            ->assertOk()
            ->assertSee('Maria Santos')
            ->assertSee('property@isufst.edu.ph');

        // And the account works: the office signs in with what was chosen
        // here, without an approval step or a verification link.
        $this->post('/logout');

        $this->post('/login', [
            'email' => 'property@isufst.edu.ph',
            'password' => 'password123',
        ])->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($account);
    }

    /**
     * Signing in with an account made in Add Office lands the desk in its own
     * workspace - the typed office gets the generic one - and never through
     * the personal-details step, because admin-created staff did not register.
     */
    public function test_an_added_office_account_lands_in_its_own_workspace(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Maria Santos',
            'email' => 'property@isufst.edu.ph',
            'role' => 'office',
            'office' => 'Property Management Office',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasNoErrors();

        $this->post('/logout');

        $this->post('/login', [
            'email' => 'property@isufst.edu.ph',
            'password' => 'password123',
        ])->assertSessionHasNoErrors();

        $this->assertAuthenticated();

        // Not the student dashboard, and not the onboarding form.
        $this->get('/dashboard')->assertRedirect('/workspace');

        $this->get('/workspace')
            ->assertOk()
            ->assertSee('Office Workspace')
            ->assertSee('Property Management Office');
    }

    /**
     * The other account type the form offers: a college account goes to its
     * own college's workspace, not to the generic one.
     */
    public function test_an_added_college_account_lands_on_its_college_page(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Pedro Cruz',
            'email' => 'cici.head@isufst.edu.ph',
            'role' => 'department',
            'office' => 'CICI',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasNoErrors();

        $this->post('/logout');

        $this->post('/login', [
            'email' => 'cici.head@isufst.edu.ph',
            'password' => 'password123',
        ])->assertSessionHasNoErrors();

        $this->get('/dashboard')->assertRedirect('/cici');

        $this->get('/cici')->assertOk();
    }

    /**
     * The directory holds the office; the login is made in User Management.
     * Adding the office here asks for no account details at all.
     */
    public function test_adding_an_office_asks_for_no_login_details(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post('/admin/offices', ['name' => 'Guidance Office'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('offices', ['name' => 'Guidance Office']);
        $this->assertDatabaseCount('users', 1);

        // The empty card points the administrator at the one place an
        // account is created.
        $this->get('/admin/offices')
            ->assertOk()
            ->assertSee('Add Office account')
            ->assertDontSee('name="create_account"', false);
    }

    /**
     * An office is registered once: the directory row the account is scoped
     * to cannot be duplicated by typing the name again.
     */
    public function test_an_office_name_can_only_be_added_once(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->post('/admin/offices', ['name' => 'Guidance Office']);

        $this->actingAs($admin)
            ->post('/admin/offices', ['name' => 'Guidance Office'])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Office::where('name', 'Guidance Office')->count());
    }

    /**
     * Only an administrator reaches the form; an office account of its own
     * cannot add offices or make itself company.
     */
    public function test_an_office_account_cannot_open_the_office_settings_page(): void
    {
        $office = User::factory()->create([
            'role' => User::ROLE_OFFICE,
            'office' => 'Library',
            'approval_status' => User::APPROVAL_APPROVED,
        ]);

        $this->actingAs($office)
            ->get('/admin/offices')
            ->assertForbidden();
    }

    public function test_admin_can_save_system_configuration(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->put('/admin/settings', [
                'institution_name' => 'ISUFST',
                'institution_address' => 'Tiwi, Barotac Nuevo',
                'academic_term' => '2nd Semester, AY 2026-2027',
                'support_email' => 'admin@isufst.edu.ph',
                'support_phone' => '(033) 555-0000',
                'banner_enabled' => '1',
                'banner_text' => 'Offices closed on Aug 25-26.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertEquals('ISUFST', Setting::get('institution_name'));
        $this->assertEquals('true', Setting::get('banner_enabled'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'settings.updated']);
    }

    public function test_admin_can_view_audit_logs_with_filters(): void
    {
        $admin = $this->makeAdmin();

        AuditLog::create([
            'user_id' => $admin->id,
            'actor_name' => $admin->name,
            'role' => 'admin',
            'action' => 'user.created',
            'description' => 'Created registrar account.',
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($admin)
            ->get('/admin/audits')
            ->assertOk()
            ->assertSee('Master Logs')
            ->assertSee('user.created');

        $this->actingAs($admin)
            ->get('/admin/audits?action=user.created')
            ->assertOk()
            ->assertSee('user.created');
    }

    public function test_student_cannot_access_admin_routes(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        foreach (['/admin/users', '/admin/offices', '/admin/settings', '/admin/audits'] as $url) {
            $this->actingAs($student)
                ->get($url)
                ->assertForbidden();
        }
    }

    public function test_admin_dashboard_shows_system_metrics(): void
    {
        $admin = $this->makeAdmin();
        User::factory()->count(3)->create(['role' => 'student']);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Total Accounts');
    }

    public function test_admin_dashboard_office_filter_scopes_the_panels(): void
    {
        $admin = $this->makeAdmin();

        $makeAppointment = function (string $office, string $purpose): Appointment {
            $student = User::factory()->create(['role' => 'student']);

            return $student->appointments()->create([
                'office' => $office,
                'purpose' => $purpose,
                'date' => now()->addDays(2)->toDateString(),
                'time_slot' => '09:00 AM - 10:00 AM',
                'status' => 'pending',
            ]);
        };

        $registrarAppt = $makeAppointment('Registrar', 'Certification');
        $libraryAppt = $makeAppointment('Library', 'Library Clearance');

        ConsultationService::create([
            'office' => 'Registrar',
            'name' => 'Enrollment Consultation',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        ConsultationService::create([
            'office' => 'Library',
            'name' => 'Thesis Consultation',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // No filter: both offices appear on the master view. The services
        // panel also renders each row (and its master toggle form).
        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee($registrarAppt->reference_code)
            ->assertSee($libraryAppt->reference_code)
            ->assertSee('Enrollment Consultation')
            ->assertSee('Thesis Consultation');

        // Scoped to Library: only the Library records remain.
        $this->actingAs($admin)
            ->get('/dashboard?office=Library')
            ->assertOk()
            ->assertSee($libraryAppt->reference_code)
            ->assertDontSee($registrarAppt->reference_code)
            ->assertSee('Thesis Consultation')
            ->assertDontSee('Enrollment Consultation');
    }

    public function test_admin_can_toggle_a_consultation_service_across_offices(): void
    {
        $admin = $this->makeAdmin();

        $service = ConsultationService::create([
            'office' => 'Library',
            'name' => 'Thesis Consultation',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->patch("/admin/consultations/{$service->id}/toggle")
            ->assertRedirect();

        $this->assertFalse($service->fresh()->is_active);
    }

    public function test_student_dashboard_shows_settings_banner(): void
    {
        Setting::query()->updateOrCreate(['key' => 'banner_enabled'], ['value' => 'true']);
        Setting::query()->updateOrCreate(['key' => 'banner_text'], ['value' => 'Offices closed on Aug 25-26.']);

        $student = User::factory()->create(['role' => 'student', 'contact_number' => '09171234567']);

        $this->actingAs($student)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Offices closed on Aug 25-26.');
    }
}
