<?php

namespace Tests\Feature;

use App\Enums\Permission as PermissionEnum;
use App\Enums\Role as RoleEnum;
use App\Models\Document;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DocumentsSeeder;
use Database\Seeders\OsasOfficeSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Distinct roles & permissions for the four public account types
 * (student, alumni, parent, guest): the roles/permissions catalog, the
 * document audiences, and the offices each type may book.
 */
class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'student', ?string $registrationType = null): User
    {
        return User::factory()->create([
            'role' => $role,
            'registration_type' => $registrationType,
        ]);
    }

    /**
     * @return array{student: Document, alumni: Document, both: Document}
     */
    private function documents(): array
    {
        return [
            'student' => Document::create([
                'name' => 'Good Moral Character',
                'fee' => 50,
                'audience' => Document::AUDIENCE_STUDENT,
            ]),
            'alumni' => Document::create([
                'name' => 'Diploma Replacement',
                'fee' => 250,
                'audience' => Document::AUDIENCE_ALUMNI,
            ]),
            'both' => Document::create([
                'name' => 'Transcript of Record (TOR)',
                'fee' => 100,
                'audience' => Document::AUDIENCE_BOTH,
            ]),
        ];
    }

    private function docPayload(array $documentIds, array $overrides = []): array
    {
        return array_replace([
            'document_ids' => $documentIds,
            'purpose_type' => 'employment',
            'educational_status' => 'not_graduated',
            'educational_level' => 'college',
            'claim_mode' => 'personal',
        ], $overrides);
    }

    // ---------------------------------------------------------------------
    // Catalog
    // ---------------------------------------------------------------------

    public function test_roles_and_permissions_tables_mirror_the_enums(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertSame(count(RoleEnum::cases()), Role::count());
        $this->assertSame(count(PermissionEnum::cases()), Permission::count());

        foreach (RoleEnum::userTypes() as $role) {
            $this->assertFalse(
                Role::where('name', $role->value)->firstOrFail()->is_staff,
                "{$role->value} must be flagged as a public account type"
            );
        }

        foreach (RoleEnum::staff() as $role) {
            $this->assertTrue(
                Role::where('name', $role->value)->firstOrFail()->is_staff,
                "{$role->value} must be flagged as a staff role"
            );
        }

        // The permission_role pivot matches the Role enum exactly.
        foreach (RoleEnum::cases() as $role) {
            $model = Role::where('name', $role->value)->firstOrFail();

            $this->assertEqualsCanonicalizing(
                $role->permissionNames(),
                $model->permissions()->pluck('name')->all(),
                "{$role->value} permissions drifted from the enum"
            );
        }
    }

    public function test_user_types_resolve_to_distinct_rbac_roles(): void
    {
        $this->assertSame(RoleEnum::Student, $this->user('student')->rbacRole());
        $this->assertSame(RoleEnum::Alumni, $this->user('student', 'alumni')->rbacRole());
        $this->assertSame(RoleEnum::Parent, $this->user('student', 'parent')->rbacRole());
        $this->assertSame(RoleEnum::Guest, $this->user('student', 'guest')->rbacRole());
        $this->assertSame(RoleEnum::Registrar, $this->user('registrar')->rbacRole());
    }

    public function test_students_hold_academic_but_not_alumni_document_permission(): void
    {
        $student = $this->user('student');
        $this->assertTrue($student->hasPermission(PermissionEnum::RequestAcademicDocuments));
        $this->assertFalse($student->hasPermission(PermissionEnum::RequestAlumniDocuments));

        $alumni = $this->user('student', 'alumni');
        $this->assertTrue($alumni->hasPermission(PermissionEnum::RequestAlumniDocuments));
        $this->assertFalse($alumni->hasPermission(PermissionEnum::RequestAcademicDocuments));
    }

    // ---------------------------------------------------------------------
    // Documents
    // ---------------------------------------------------------------------

    public function test_student_request_page_only_lists_student_documents(): void
    {
        $documents = $this->documents();

        $this->actingAs($this->user('student'))
            ->get('/student/document-requests/create')
            ->assertOk()
            ->assertSee($documents['student']->name)
            ->assertSee($documents['both']->name)
            ->assertDontSee($documents['alumni']->name);
    }

    public function test_student_cannot_request_an_alumni_only_document(): void
    {
        $documents = $this->documents();

        $this->actingAs($this->user('student'))
            ->post('/student/document-requests', $this->docPayload([$documents['alumni']->id]))
            ->assertForbidden();

        $this->assertDatabaseCount('document_requests', 0);
    }

    public function test_alumni_request_page_only_lists_alumni_documents(): void
    {
        $documents = $this->documents();

        $this->actingAs($this->user('student', 'alumni'))
            ->get('/student/document-requests/create')
            ->assertOk()
            ->assertSee($documents['alumni']->name)
            ->assertSee($documents['both']->name)
            ->assertDontSee($documents['student']->name);
    }

    public function test_alumni_request_requires_graduation_details(): void
    {
        $documents = $this->documents();

        $this->actingAs($this->user('student', 'alumni'))
            ->post('/student/document-requests', $this->docPayload([$documents['alumni']->id]))
            ->assertSessionHasErrors(['year_graduated', 'course']);

        $this->assertDatabaseCount('document_requests', 0);
    }

    public function test_alumni_request_saves_graduation_details_to_account(): void
    {
        Mail::fake();
        Notification::fake();

        $documents = $this->documents();
        $alumni = $this->user('student', 'alumni');

        $this->actingAs($alumni)
            ->post('/student/document-requests', $this->docPayload([$documents['alumni']->id], [
                'year_graduated' => '2023',
                'course' => 'BS Information Technology',
            ]))
            ->assertRedirect(route('student.documents.index'));

        $alumni->refresh();
        $this->assertSame('2023', $alumni->year_graduated);
        $this->assertSame('BS Information Technology', $alumni->course);

        $request = $alumni->documentRequests()->firstOrFail();
        $this->assertSame('BS Information Technology 2023', $request->student_course_year);
    }

    public function test_parent_and_guest_cannot_reach_document_requests(): void
    {
        $this->actingAs($this->user('student', 'parent'))
            ->get('/student/document-requests/create')
            ->assertForbidden();

        $this->actingAs($this->user('student', 'guest'))
            ->get('/student/document-requests/create')
            ->assertForbidden();
    }

    public function test_parent_request_hub_hides_the_document_card(): void
    {
        $this->actingAs($this->user('student', 'parent'))
            ->get('/student/requests/new')
            ->assertOk()
            ->assertSee('/student/appointments/create')
            ->assertDontSee('/student/document-requests/create');
    }

    public function test_student_request_hub_shows_the_document_card(): void
    {
        $this->actingAs($this->user('student'))
            ->get('/student/requests/new')
            ->assertOk()
            ->assertSee('/student/appointments/create')
            ->assertSee('/student/document-requests/create');
    }

    public function test_document_seeder_assigns_role_audiences(): void
    {
        $this->seed(DocumentsSeeder::class);

        $this->assertSame('student', Document::where('name', 'Certificate of Grades (COG)')->value('audience'));
        $this->assertSame('alumni', Document::where('name', 'Diploma')->value('audience'));
        $this->assertSame('student', Document::where('name', 'Good Moral Character')->value('audience'));
        $this->assertSame('student', Document::where('name', 'Permanent Record (F-137-A)')->value('audience'));
        $this->assertSame('both', Document::where('name', 'Transcript of Record (TOR)')->value('audience'));
    }

    public function test_student_can_request_cog_but_alumni_cannot(): void
    {
        $this->seed(DocumentsSeeder::class);

        $this->actingAs($this->user('student'))
            ->get('/student/document-requests/create')
            ->assertOk()
            ->assertSee('Certificate of Grades (COG)');

        $this->actingAs($this->user('student', 'alumni'))
            ->get('/student/document-requests/create')
            ->assertOk()
            ->assertDontSee('Certificate of Grades (COG)');
    }

    // ---------------------------------------------------------------------
    // Appointments
    // ---------------------------------------------------------------------

    public function test_booking_page_shows_all_nine_offices(): void
    {
        // All nine offices are available to every user role.
        $this->actingAs($this->user('student'))
            ->get('/student/appointments/create')
            ->assertOk()
            ->assertSee('Student Support Center (SSC)')
            ->assertSee('Registrar')
            ->assertSee('Accounting')
            ->assertSee('Cashier')
            ->assertSee('Library')
            ->assertSee('CICI')
            ->assertSee('CBMSD')
            ->assertSee('COAG')
            ->assertSee('COED');
    }

    public function test_student_can_book_any_office(): void
    {
        Mail::fake();
        Notification::fake();

        // Students now have access to all nine offices; booking Accounting
        // should succeed (redirect) rather than be forbidden.
        $this->actingAs($this->user('student'))
            ->post('/student/appointments', [
                'office' => 'Accounting',
                'purpose' => 'Payment Concerns',
                'date' => now()->addDays(3)->toDateString(),
                'time_slot' => '09:00 AM - 10:00 AM',
            ])
            ->assertRedirect(route('student.appointments.index'));
    }

    public function test_parent_booking_page_asks_for_the_childs_student_id(): void
    {
        $this->actingAs($this->user('student', 'parent'))
            ->get('/student/appointments/create')
            ->assertOk()
            ->assertSee('name="student_id"', false);
    }

    public function test_guest_booking_page_asks_for_a_valid_id(): void
    {
        $this->actingAs($this->user('student', 'guest'))
            ->get('/student/appointments/create')
            ->assertOk()
            ->assertSee('name="valid_id"', false);
    }

    public function test_parent_booking_requires_child_student_id(): void
    {
        Mail::fake();
        Notification::fake();

        $this->actingAs($this->user('student', 'parent'))
            ->post('/student/appointments', [
                'office' => 'SSC',
                'purpose' => 'General Inquiry',
                'date' => now()->addDays(3)->toDateString(),
                'time_slot' => '09:00 AM - 10:00 AM',
            ])
            ->assertSessionHasErrors('student_id');

        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_parent_booking_saves_the_childs_student_id(): void
    {
        Mail::fake();
        Notification::fake();

        $parent = $this->user('student', 'parent');

        $this->actingAs($parent)
            ->post('/student/appointments', [
                'office' => 'SSC',
                'purpose' => 'General Inquiry',
                'date' => now()->addDays(3)->toDateString(),
                'time_slot' => '09:00 AM - 10:00 AM',
                'student_id' => '2024-00123',
            ])
            ->assertRedirect(route('student.appointments.index'));

        $this->assertSame('2024-00123', $parent->refresh()->student_id);
    }

    public function test_guest_booking_requires_a_valid_id_and_saves_it(): void
    {
        Mail::fake();
        Notification::fake();

        $guest = $this->user('student', 'guest');

        $this->actingAs($guest)
            ->post('/student/appointments', [
                'office' => 'CICI',
                'purpose' => 'General Inquiry',
                'date' => now()->addDays(3)->toDateString(),
                'time_slot' => '09:00 AM - 10:00 AM',
            ])
            ->assertSessionHasErrors('valid_id');

        $this->assertDatabaseCount('appointments', 0);

        $this->actingAs($guest)
            ->post('/student/appointments', [
                'office' => 'CICI',
                'purpose' => 'General Inquiry',
                'date' => now()->addDays(3)->toDateString(),
                'time_slot' => '09:00 AM - 10:00 AM',
                'valid_id' => "Driver's License N01-23-456789",
            ])
            ->assertRedirect(route('student.appointments.index'));

        $this->assertSame("Driver's License N01-23-456789", $guest->refresh()->valid_id);
    }

    public function test_guest_can_book_the_registrar(): void
    {
        Mail::fake();
        Notification::fake();

        $guest = $this->user('student', 'guest');
        $guest->forceFill(['valid_id' => 'Passport P123456'])->save();

        // The booking form offers the same nine offices to every role, so a
        // guest reaches the Registrar like anyone else.
        $this->actingAs($guest)
            ->post('/student/appointments', [
                'office' => 'Registrar',
                'purpose' => 'Transcript of Records',
                'date' => now()->addDays(3)->toDateString(),
            ])
            ->assertRedirect(route('student.appointments.index'));

        $this->assertDatabaseCount('appointments', 1);
    }

    // ---------------------------------------------------------------------
    // Office accounts
    // ---------------------------------------------------------------------

    /**
     * The OSAS desk used to be seeded with a bespoke 'osas' role that the
     * catalog does not know, so it resolved to Student — student permissions
     * and the student portal — while role:department,office kept it out of
     * the office workspace entirely. It is now the Student Support Center:
     * the account keeps its legacy email but books and works as SSC.
     */
    public function test_seeded_ssc_office_account_holds_the_office_role(): void
    {
        $this->seed(OsasOfficeSeeder::class);

        $ssc = User::where('email', 'osas@isufst.edu.ph')->firstOrFail();

        $this->assertSame('office', $ssc->role);
        $this->assertSame('SSC', $ssc->office);
        $this->assertSame(RoleEnum::Office, $ssc->rbacRole());
        $this->assertSame('SSC', $ssc->officeScope());
        $this->assertFalse($ssc->canRequestDocuments());
    }

    public function test_office_role_accounts_land_on_the_office_workspace(): void
    {
        $office = User::factory()->create(['role' => 'office', 'office' => 'SSC']);

        $this->actingAs($office)
            ->get('/dashboard')
            ->assertRedirect(route('workspace.dashboard'));
    }
}
