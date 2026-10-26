<?php

namespace Tests\Feature;

use App\Models\ConsultationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    private function makeStaff(string $office, string $role = 'department'): User
    {
        return User::factory()->create([
            'role' => $role,
            'office' => $office,
        ]);
    }

    private function makeStudent(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    public function test_office_page_shows_only_its_own_services(): void
    {
        $this->makeStaff('Accounting', 'accounting');
        ConsultationService::create(['office' => 'Accounting', 'name' => 'Payment Concerns']);
        ConsultationService::create(['office' => 'Library', 'name' => 'Research Assistance']);

        $this->actingAs($this->makeStaff('Accounting', 'accounting'))
            ->get('/accounting/consultations')
            ->assertOk()
            ->assertSee('Payment Concerns')
            ->assertDontSee('Research Assistance');
    }

    public function test_staff_can_add_a_service(): void
    {
        $this->actingAs($this->makeStaff('Accounting', 'accounting'))
            ->post('/accounting/consultations', [
                'name' => 'Career Coaching',
                'description' => 'One-on-one career coaching.',
                'is_active' => '1',
            ])
            ->assertRedirect(route('accounting.consultations'))
            ->assertSessionHas('status');

        $service = ConsultationService::firstWhere('name', 'Career Coaching');

        $this->assertNotNull($service);
        $this->assertSame('Accounting', $service->office);
        $this->assertTrue($service->is_active);
    }

    public function test_service_is_owned_by_the_signed_in_office_not_the_url(): void
    {
        // A department account is allowed through /cici by role, but the row
        // must still be owned by the account's own office.
        $this->actingAs($this->makeStaff('CBMSD'))
            ->post('/cici/consultations', ['name' => 'Capstone Advising'])
            ->assertRedirect();

        $service = ConsultationService::firstWhere('name', 'Capstone Advising');

        $this->assertNotNull($service);
        $this->assertSame('CBMSD', $service->office);
    }

    public function test_staff_can_update_their_own_service(): void
    {
        $service = ConsultationService::create([
            'office' => 'SSC',
            'name' => 'Scholarship',
        ]);

        $this->actingAs($this->makeStaff('SSC', 'ssc'))
            ->put('/ssc/consultations/'.$service->id, [
                'name' => 'Scholarship & Aid',
                'is_active' => '0',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $service->refresh();

        $this->assertSame('Scholarship & Aid', $service->name);
        $this->assertFalse($service->is_active);
    }

    public function test_staff_cannot_update_another_offices_service(): void
    {
        $service = ConsultationService::create(['office' => 'Library', 'name' => 'Book Borrowing']);

        $this->actingAs($this->makeStaff('SSC', 'ssc'))
            ->put('/ssc/consultations/'.$service->id, ['name' => 'Hijacked'])
            ->assertForbidden();

        $this->assertSame('Book Borrowing', $service->fresh()->name);
    }

    public function test_staff_cannot_delete_another_offices_service(): void
    {
        $service = ConsultationService::create(['office' => 'Library', 'name' => 'Book Borrowing']);

        $this->actingAs($this->makeStaff('SSC', 'ssc'))
            ->delete('/ssc/consultations/'.$service->id)
            ->assertForbidden();

        $this->assertNotNull($service->fresh());
    }

    public function test_staff_can_delete_their_own_service(): void
    {
        $service = ConsultationService::create(['office' => 'COED', 'name' => 'Practicum Advising']);

        $this->actingAs($this->makeStaff('COED'))
            ->delete('/coed/consultations/'.$service->id)
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertNull($service->fresh());
    }

    public function test_service_name_is_required(): void
    {
        $this->actingAs($this->makeStaff('COAG'))
            ->from('/coag/consultations/create')
            ->post('/coag/consultations', ['name' => ''])
            ->assertRedirect('/coag/consultations/create')
            ->assertSessionHasErrors('name');

        $this->assertSame(0, ConsultationService::count());
    }

    public function test_student_cannot_reach_office_consultation_routes(): void
    {
        $this->actingAs($this->makeStudent())
            ->get('/accounting/consultations')
            ->assertForbidden();

        $this->actingAs($this->makeStudent())
            ->post('/accounting/consultations', ['name' => 'Nope'])
            ->assertForbidden();
    }

    public function test_create_form_renders_for_staff(): void
    {
        $this->actingAs($this->makeStaff('Accounting', 'accounting'))
            ->get('/accounting/consultations/create')
            ->assertOk()
            ->assertSee('Add Consultation Service');
    }

    public function test_every_office_and_department_page_renders(): void
    {
        $pages = [
            '/cici/consultations' => ['CICI', 'department'],
            '/cbmsd/consultations' => ['CBMSD', 'department'],
            '/coag/consultations' => ['COAG', 'department'],
            '/coed/consultations' => ['COED', 'department'],
            '/ssc/consultations' => ['SSC', 'ssc'],
            '/accounting/consultations' => ['Accounting', 'accounting'],
            '/library/consultations' => ['Library', 'library'],
        ];

        foreach ($pages as [$office]) {
            ConsultationService::create(['office' => $office, 'name' => $office.' Marker']);
        }

        $this->assertSame(count($pages), ConsultationService::count());

        foreach ($pages as $url => [$office, $role]) {
            $this->actingAs($this->makeStaff($office, $role))
                ->get($url)
                ->assertOk()
                ->assertSee('Add service')
                ->assertSee($office.' Marker')
                ->assertSee('Edit')
                ->assertSee('Deactivate');
        }
    }

    /**
     * Offices can pull a service from the catalogue without deleting it:
     * the quick Deactivate button on the list flips is_active off, and
     * Reactivate puts it back.
     */
    public function test_staff_can_deactivate_and_reactivate_their_own_service(): void
    {
        $service = ConsultationService::create([
            'office' => 'Accounting',
            'name' => 'Payment Concerns',
            'is_active' => true,
        ]);

        $staff = $this->makeStaff('Accounting', 'accounting');

        $this->actingAs($staff)
            ->patch('/accounting/consultations/'.$service->id.'/toggle')
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertFalse($service->fresh()->is_active);

        $this->actingAs($staff)
            ->patch('/accounting/consultations/'.$service->id.'/toggle')
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertTrue($service->fresh()->is_active);
    }

    public function test_a_deactivated_service_disappears_from_the_student_catalogue(): void
    {
        $service = ConsultationService::create([
            'office' => 'Accounting',
            'name' => 'Balance Consultation',
            'is_active' => true,
        ]);

        $this->actingAs($this->makeStudent())
            ->get('/student/consultations')
            ->assertOk()
            ->assertSee('Balance Consultation');

        $this->actingAs($this->makeStaff('Accounting', 'accounting'))
            ->patch('/accounting/consultations/'.$service->id.'/toggle');

        $this->actingAs($this->makeStudent())
            ->get('/student/consultations')
            ->assertOk()
            ->assertDontSee('Balance Consultation');
    }

    public function test_staff_cannot_toggle_another_offices_service(): void
    {
        $service = ConsultationService::create([
            'office' => 'Library',
            'name' => 'Book Borrowing',
            'is_active' => true,
        ]);

        $this->actingAs($this->makeStaff('SSC', 'ssc'))
            ->patch('/ssc/consultations/'.$service->id.'/toggle')
            ->assertForbidden();

        $this->assertTrue($service->fresh()->is_active);
    }

    /**
     * The list offers Reactivate once a service is hidden — the confirm
     * dialog is only wired for the destructive direction.
     */
    public function test_the_list_swaps_the_button_label_with_the_state(): void
    {
        $service = ConsultationService::create([
            'office' => 'COED',
            'name' => 'Practicum Advising',
            'is_active' => true,
        ]);

        $staff = $this->makeStaff('COED');

        $this->actingAs($staff)
            ->get('/coed/consultations')
            ->assertOk()
            ->assertSee('Deactivate')
            ->assertDontSee('Reactivate');

        $this->actingAs($staff)
            ->patch('/coed/consultations/'.$service->id.'/toggle');

        $this->actingAs($staff)
            ->get('/coed/consultations')
            ->assertOk()
            ->assertSee('Reactivate')
            ->assertDontSee('Deactivate');
    }

    /**
     * The SSC account is a generic role 'office' staff account
     * (office = 'SSC'): its workspace lives under /workspace, while /ssc is
     * the role:ssc group. The shared create/edit form must therefore target
     * the group the request came in on — deriving it from the office name
     * sent the SSC form to /ssc/consultations and answered 403.
     */
    public function test_the_ssc_office_account_can_add_a_service_from_its_workspace(): void
    {
        $staff = User::factory()->create(['role' => 'office', 'office' => 'SSC']);

        $this->actingAs($staff)
            ->get('/workspace/consultations/create')
            ->assertOk()
            ->assertSee('action="'.route('workspace.consultations.store').'"', false);

        $this->actingAs($staff)
            ->post('/workspace/consultations', [
                'name' => 'Scholarship & Financial Assistance',
                'description' => 'Student scholarship help desk.',
                'is_active' => '1',
            ])
            ->assertRedirect(route('workspace.consultations'))
            ->assertSessionHas('status');

        $service = ConsultationService::firstWhere('name', 'Scholarship & Financial Assistance');

        $this->assertNotNull($service);
        $this->assertSame('SSC', $service->office);
    }

    public function test_the_ssc_office_account_can_edit_its_service_from_its_workspace(): void
    {
        $staff = User::factory()->create(['role' => 'office', 'office' => 'SSC']);
        $service = ConsultationService::create(['office' => 'SSC', 'name' => 'Scholarship']);

        $this->actingAs($staff)
            ->get('/workspace/consultations/'.$service->id.'/edit')
            ->assertOk()
            ->assertSee(route('workspace.consultations.update', $service));

        $this->actingAs($staff)
            ->put('/workspace/consultations/'.$service->id, ['name' => 'Scholarship & Aid'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame('Scholarship & Aid', $service->fresh()->name);
    }

    /**
     * /ssc stays the role:ssc group — a role 'office' account does not get to
     * borrow it, which is why the form must target the workspace group.
     */
    public function test_a_role_office_account_cannot_post_to_the_ssc_group(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'office', 'office' => 'SSC']))
            ->post('/ssc/consultations', ['name' => 'Not allowed'])
            ->assertForbidden();

        $this->assertSame(0, ConsultationService::count());
    }

    public function test_students_cannot_toggle_services(): void
    {
        $service = ConsultationService::create([
            'office' => 'Accounting',
            'name' => 'Payment Concerns',
            'is_active' => true,
        ]);

        $this->actingAs($this->makeStudent())
            ->patch('/accounting/consultations/'.$service->id.'/toggle')
            ->assertForbidden();

        $this->assertTrue($service->fresh()->is_active);
    }
}
