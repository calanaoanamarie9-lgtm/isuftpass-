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
        $this->actingAs($this->makeStaff('Guidance', 'guidance'))
            ->post('/guidance/consultations', [
                'name' => 'Career Coaching',
                'description' => 'One-on-one career coaching.',
                'is_active' => '1',
            ])
            ->assertRedirect(route('guidance.consultations'))
            ->assertSessionHas('status');

        $service = ConsultationService::firstWhere('name', 'Career Coaching');

        $this->assertNotNull($service);
        $this->assertSame('Guidance', $service->office->value);
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
        $this->assertSame('CBMSD', $service->office->value);
    }

    public function test_staff_can_update_their_own_service(): void
    {
        $service = ConsultationService::create([
            'office' => 'OSAS',
            'name' => 'Scholarship',
        ]);

        $this->actingAs($this->makeStaff('OSAS', 'osas'))
            ->put('/osas/consultations/' . $service->id, [
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

        $this->actingAs($this->makeStaff('OSAS', 'osas'))
            ->put('/osas/consultations/' . $service->id, ['name' => 'Hijacked'])
            ->assertForbidden();

        $this->assertSame('Book Borrowing', $service->fresh()->name);
    }

    public function test_staff_cannot_delete_another_offices_service(): void
    {
        $service = ConsultationService::create(['office' => 'Library', 'name' => 'Book Borrowing']);

        $this->actingAs($this->makeStaff('OSAS', 'osas'))
            ->delete('/osas/consultations/' . $service->id)
            ->assertForbidden();

        $this->assertNotNull($service->fresh());
    }

    public function test_staff_can_delete_their_own_service(): void
    {
        $service = ConsultationService::create(['office' => 'COED', 'name' => 'Practicum Advising']);

        $this->actingAs($this->makeStaff('COED'))
            ->delete('/coed/consultations/' . $service->id)
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
            '/osas/consultations' => ['OSAS', 'osas'],
            '/accounting/consultations' => ['Accounting', 'accounting'],
            '/library/consultations' => ['Library', 'library'],
            '/guidance/consultations' => ['Guidance', 'guidance'],
        ];

        foreach ($pages as [$office]) {
            ConsultationService::create(['office' => $office, 'name' => $office . ' Marker']);
        }

        $this->assertSame(count($pages), ConsultationService::count());

        foreach ($pages as $url => [$office, $role]) {
            $this->actingAs($this->makeStaff($office, $role))
                ->get($url)
                ->assertOk()
                ->assertSee('Add service')
                ->assertSee($office . ' Marker')
                ->assertSee('Edit');
        }
    }
}
