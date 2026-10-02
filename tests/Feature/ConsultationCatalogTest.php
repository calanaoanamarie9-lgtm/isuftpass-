<?php

namespace Tests\Feature;

use App\Models\ConsultationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudent(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    public function test_catalog_lists_active_services_grouped_by_office(): void
    {
        ConsultationService::create(['office' => 'Accounting', 'name' => 'Payment Concerns']);
        ConsultationService::create(['office' => 'Library', 'name' => 'Research Assistance']);

        $this->actingAs($this->makeStudent())
            ->get('/student/consultations')
            ->assertOk()
            ->assertSee('Payment Concerns')
            ->assertSee('Research Assistance')
            ->assertSee('Consultation Services');
    }

    public function test_catalog_hides_inactive_services(): void
    {
        ConsultationService::create(['office' => 'Guidance', 'name' => 'Visible Service']);
        ConsultationService::create([
            'office' => 'Guidance',
            'name' => 'Hidden Service',
            'is_active' => false,
        ]);

        $this->actingAs($this->makeStudent())
            ->get('/student/consultations')
            ->assertOk()
            ->assertSee('Visible Service')
            ->assertDontSee('Hidden Service');
    }

    public function test_service_card_links_to_a_prefilled_booking(): void
    {
        ConsultationService::create(['office' => 'OSAS', 'name' => 'Scholarship Inquiry']);

        $response = $this->actingAs($this->makeStudent())->get('/student/consultations');

        $response->assertOk();

        $expected = route('student.appointments.create', [
            'office' => 'OSAS',
            'purpose' => 'Scholarship Inquiry',
        ]);

        $this->assertStringContainsString('office=OSAS', $expected);
        $this->assertStringContainsString('purpose=Scholarship%20Inquiry', $expected);

        // Blade escapes the & in the href, so assertSee's default escaping matches.
        $response->assertSee($expected);
    }

    public function test_booking_form_prefills_office_and_purpose_from_the_query_string(): void
    {
        $this->actingAs($this->makeStudent())
            ->get('/student/appointments/create?office=OSAS&purpose=Scholarship%20Inquiry')
            ->assertOk()
            ->assertSee('Scholarship Inquiry')
            ->assertSee('value="OSAS"', false);
    }

    public function test_empty_catalog_shows_a_helpful_state(): void
    {
        $this->actingAs($this->makeStudent())
            ->get('/student/consultations')
            ->assertOk()
            ->assertSee('No consultation services yet');
    }
}
