<?php

namespace Tests\Feature;

use App\Models\ConsultationService;
use App\Models\User;
use App\Support\AppointmentPurposes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * The booking form's Purpose dropdown is fed live from the services each
 * office manages in its own panel: choosing an office asks
 * GET /student/appointments/services for that office's active rows and
 * rebuilds the list from the answer, so an office adding, renaming,
 * deactivating or deleting a service changes the student's form on the very
 * next request — no hardcoded list to keep in step.
 *
 * Offices with a services panel (the four colleges, SSC, Accounting, Library
 * and every self-registered office account) answer from the database alone:
 * an empty panel offers only "Other" in the Purpose dropdown. Offices without
 * a panel (the Registrar's document counter, the Cashier) keep answering from
 * their curated list.
 */
class AppointmentPurposeServicesTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudent(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    private function servicesFor(string $office): array
    {
        $response = $this->actingAs($this->makeStudent())
            ->getJson(route('student.appointments.services', ['office' => $office]))
            ->assertOk()
            ->assertJsonPath('office', $office);

        return array_column($response->json('services'), 'name');
    }

    public function test_the_endpoint_returns_only_the_active_services_of_the_office_asked_for(): void
    {
        ConsultationService::create(['office' => 'Library', 'name' => 'Reader Services', 'is_active' => true]);
        ConsultationService::create(['office' => 'Library', 'name' => 'Discontinued Service', 'is_active' => false]);
        ConsultationService::create(['office' => 'Accounting', 'name' => 'Payment Concerns', 'is_active' => true]);

        $this->assertSame(['Reader Services'], $this->servicesFor('Library'));
    }

    public function test_a_service_an_office_adds_or_deactivates_shows_up_without_a_deploy(): void
    {
        $service = ConsultationService::create([
            'office' => 'CICI',
            'name' => 'Practicum Advising',
            'is_active' => true,
        ]);

        $this->assertSame(['Practicum Advising'], $this->servicesFor('CICI'));

        // The office renames it in its panel: the next request already
        // answers with the new name.
        $service->update(['name' => 'Internship Advising']);

        $this->assertSame(['Internship Advising'], $this->servicesFor('CICI'));

        // ...and deactivating it takes it straight out of the dropdown.
        $service->update(['is_active' => false]);

        $this->assertSame([], $this->servicesFor('CICI'));
    }

    /**
     * Every bookable office must answer with its own services and never with
     * another office's — the dropdown for one office is built from one query.
     */
    public function test_every_office_pulls_only_its_own_services(): void
    {
        $offices = ['Library', 'Accounting', 'CICI', 'CBMSD', 'COAG', 'COED'];

        foreach ($offices as $office) {
            ConsultationService::create(['office' => $office, 'name' => $office.' Marker', 'is_active' => true]);
            ConsultationService::create(['office' => $office, 'name' => $office.' Retired', 'is_active' => false]);
        }

        // The Cashier keeps a curated list too — it runs a payment counter,
        // not a panel of services (see the fallback test below).
        foreach ($offices as $office) {
            $this->assertSame([$office.' Marker'], $this->servicesFor($office));
        }
    }

    public function test_an_office_without_a_services_panel_still_falls_back_to_its_curated_list(): void
    {
        // No rows exist for the Registrar, yet the dropdown must still offer
        // its standard documents — it has no panel to add services from, so
        // an empty Purpose field would dead-end the booking.
        $purposes = $this->servicesFor('Registrar');

        $this->assertContains('Transcript of Records', $purposes);
        $this->assertContains('Good Moral Certificate', $purposes);

        // The fallback answers for the office asked and for that office only.
        $this->assertNotContains('Transcript of Records', $this->servicesFor('SSC'));

        // The Cashier runs a payment counter rather than a service panel, so
        // it answers from its curated list as well.
        $this->assertContains('Payment of School Fees', $this->servicesFor('Cashier'));
    }

    /**
     * A panel office with nothing in it offers only "Other": the curated
     * baseline is retired the moment the office owns a panel, so students are
     * never shown presets the office did not create.
     */
    public function test_a_panel_office_with_no_services_offers_only_other(): void
    {
        $this->assertSame([], $this->servicesFor('SSC'));

        $html = $this->actingAs($this->makeStudent())
            ->get(route('student.appointments.create', ['office' => 'SSC']))
            ->assertOk()
            ->assertSee(AppointmentPurposes::OTHER)
            ->getContent();

        // Not one of the retired SSC presets may come back.
        foreach ([
            'Student Affairs & Welfare Concern',
            'Scholarship & Financial Assistance',
            'Student Organizations & Activities',
            'Counseling & Psychological Support',
            'Student Discipline & Clearance',
            'Grievance / Complaint Assistance',
        ] as $retired) {
            $this->assertStringNotContainsString($retired, $html);
        }
    }

    /**
     * A Registrar office account does not turn the Registrar into a panel
     * office: until it adds rows of its own, students keep the curated
     * document list rather than an empty Purpose dropdown.
     */
    public function test_a_registrar_office_account_keeps_the_curated_document_list(): void
    {
        User::factory()->create([
            'role' => 'office',
            'office' => 'Registrar',
            'approval_status' => User::APPROVAL_APPROVED,
            'is_active' => true,
        ]);

        $this->assertContains('Transcript of Records', $this->servicesFor('Registrar'));

        // ...and the moment that account adds a service, the row wins.
        ConsultationService::create(['office' => 'Registrar', 'name' => 'Enrollment Verification', 'is_active' => true]);

        $this->assertSame(['Enrollment Verification'], $this->servicesFor('Registrar'));
    }

    /**
     * The same rule covers a self-registered office account: its rows come
     * from the shared workspace panel, and no rows means only "Other".
     */
    public function test_a_self_registered_office_with_no_services_offers_only_other(): void
    {
        User::factory()->create([
            'role' => 'office',
            'office' => 'Clinic',
            'approval_status' => User::APPROVAL_APPROVED,
            'is_active' => true,
        ]);

        $this->assertSame([], AppointmentPurposes::activeServices('Clinic'));

        ConsultationService::create(['office' => 'Clinic', 'name' => 'Medical Consultation', 'is_active' => true]);

        $this->assertSame(
            ['Medical Consultation'],
            array_column(AppointmentPurposes::activeServices('Clinic'), 'name'),
        );
    }

    public function test_the_booking_form_renders_the_same_list_the_endpoint_answers(): void
    {
        ConsultationService::create(['office' => 'Library', 'name' => 'Reader Services', 'is_active' => true]);
        ConsultationService::create(['office' => 'Library', 'name' => 'Discontinued Service', 'is_active' => false]);

        $html = $this->actingAs($this->makeStudent())
            ->get(route('student.appointments.create', ['office' => 'Library']))
            ->assertOk()
            ->assertSee('Reader Services')
            ->assertDontSee('Discontinued Service')
            ->getContent();

        // The curated Library baseline is dropped the moment the office has
        // services of its own: the panel is the single source of truth.
        $this->assertNotSame(
            [],
            array_intersect(['Reader Services'], $this->servicesFor('Library')),
            'The form and the endpoint must agree on the office\'s services.'
        );

        $this->assertStringNotContainsString('Library Orientation', $html);
    }

    /**
     * The option's value has to be the purpose itself — a positional index
     * would store "0" on the appointment the office later reads.
     */
    public function test_a_purpose_option_submits_its_own_name(): void
    {
        $html = $this->actingAs($this->makeStudent())
            ->get(route('student.appointments.create', ['office' => 'Registrar']))
            ->assertOk()
            ->assertSee('Transcript of Records')
            ->getContent();

        $this->assertStringContainsString('value="Transcript of Records"', $html);
        $this->assertStringContainsString('value="'.AppointmentPurposes::OTHER.'"', $html);
    }

    public function test_the_purpose_field_is_locked_until_an_office_is_chosen(): void
    {
        $noOffice = $this->actingAs($this->makeStudent())
            ->get(route('student.appointments.create'))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<select id="purpose"[^>]*[\s"\'()]disabled(?![\w:-])/',
            $noOffice,
            'Without an office there is nothing to offer, so the Purpose dropdown must render disabled.'
        );

        $withOffice = $this->actingAs($this->makeStudent())
            ->get(route('student.appointments.create', ['office' => 'Registrar']))
            ->assertOk()
            ->getContent();

        $this->assertSame(
            0,
            preg_match('/<select id="purpose"[^>]*[\s"\'()]disabled(?![\w:-])/', $withOffice),
            'Once an office is chosen the Purpose dropdown must be usable.'
        );
    }

    /** The form must actually fetch — the server render is only the first list. */
    public function test_the_form_fetches_services_whenever_the_office_changes(): void
    {
        $html = $this->actingAs($this->makeStudent())
            ->get(route('student.appointments.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            "loadPurposes(officeEl.value, '')",
            $html,
            'Changing office must re-fetch that office\'s services.'
        );

        // "Other" is the form's escape hatch, not an office service: the
        // server map carries it for the first paint and the fetch appends it
        // to whatever comes back.
        $this->assertStringContainsString(
            "list[otherPurpose] = 'Other'",
            $html,
            'The fetched list must still end with the free-text Other option.'
        );

        $this->assertStringContainsString(
            'purposeSelect.disabled = true',
            $html,
            'The Purpose dropdown must be disabled while its services load.'
        );

        // Js::from JSON-encodes the URL, which escapes every slash.
        $servicesUrl = route('student.appointments.services');

        $this->assertTrue(
            str_contains($html, $servicesUrl) || str_contains($html, str_replace('/', '\\/', $servicesUrl)),
            'The form must know where the services endpoint lives.'
        );
    }

    public function test_the_endpoint_rejects_an_office_it_does_not_know(): void
    {
        $this->actingAs($this->makeStudent())
            ->getJson(route('student.appointments.services', ['office' => 'Registrar']))
            ->assertOk();

        $this->actingAs($this->makeStudent())
            ->getJson(route('student.appointments.services', ['office' => 'No Such Office']))
            ->assertStatus(422);

        $this->actingAs($this->makeStudent())
            ->getJson(route('student.appointments.services'))
            ->assertStatus(422);
    }

    public function test_guests_cannot_read_the_services_endpoint(): void
    {
        $this->getJson(route('student.appointments.services', ['office' => 'Registrar']))
            ->assertStatus(401);
    }

    /**
     * The edit form renders the same Purpose list as the booking form — the
     * office's live panel services. It used to be built from the curated
     * baseline, so a student editing a Library booking was offered services
     * the office had already removed from its panel.
     */
    public function test_the_edit_form_renders_the_offices_live_services_not_the_curated_list(): void
    {
        Mail::fake();
        Notification::fake();

        ConsultationService::create(['office' => 'Library', 'name' => 'Service Hours', 'is_active' => true]);
        ConsultationService::create(['office' => 'Library', 'name' => 'Discontinued Service', 'is_active' => false]);

        $student = $this->makeStudent();

        // A booking made under a purpose the office has since removed.
        $this->actingAs($student)->post(route('student.appointments.store'), [
            'office' => 'Library',
            'purpose' => 'Research Assistance',
            'date' => now()->addDays(3)->toDateString(),
        ])->assertRedirect(route('student.appointments.index'));

        $appointment = $student->appointments()->firstOrFail();

        $html = $this->actingAs($student)
            ->get(route('student.appointments.edit', $appointment))
            ->assertOk()
            ->assertSee('Service Hours')
            ->assertDontSee('Discontinued Service')
            ->getContent();

        // The curated Library baseline is gone the moment the office keeps
        // services of its own — it must not reappear on this form.
        $this->assertStringNotContainsString('Library Orientation', $html);

        // The booking's own removed purpose stays selected so it is never
        // silently swapped for another one.
        $this->assertStringContainsString('Research Assistance', $html);
    }
}
