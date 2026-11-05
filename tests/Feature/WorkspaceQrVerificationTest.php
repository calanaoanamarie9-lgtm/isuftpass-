<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\DocumentRequestStatus;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * The verification station every department and office serves at /qr.
 *
 * The registrar's station answers for the whole university; a department's
 * answers only for its own queue. What has to hold is both halves of that:
 * the desk recognises whatever pass is handed to it (identity, appointment,
 * claim slip, or a typed name), and it can never record an arrival that
 * belongs to another office.
 */
class WorkspaceQrVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** URL prefix => [office, role] for every desk that serves the page. */
    private const WORKSPACES = [
        '/cici' => ['CICI', 'department'],
        '/cbmsd' => ['CBMSD', 'department'],
        '/coag' => ['COAG', 'department'],
        '/coed' => ['COED', 'department'],
        '/ssc' => ['SSC', 'ssc'],
        '/accounting' => ['Accounting', 'accounting'],
        '/library' => ['Library', 'library'],
        '/workspace' => ['Registrar Extension', 'office'],
    ];

    private function makeStaff(string $office, string $role): User
    {
        return User::factory()->create(['role' => $role, 'office' => $office]);
    }

    private function makeStudent(array $overrides = []): User
    {
        return User::factory()->create(array_merge(['role' => 'student'], $overrides));
    }

    private function makeAppointment(User $student, string $office, array $overrides = []): Appointment
    {
        return $student->appointments()->create(array_merge([
            'office' => $office,
            'purpose' => 'Certification of grades',
            'date' => now()->toDateString(),
            'time_slot' => Appointment::TIME_SLOTS[0],
            'status' => AppointmentStatus::CONFIRMED->value,
        ], $overrides));
    }

    public function test_every_workspace_serves_the_verification_station(): void
    {
        foreach (self::WORKSPACES as $prefix => [$office, $role]) {
            $this->actingAs($this->makeStaff($office, $role))
                ->get($prefix.'/qr')
                ->assertOk()
                ->assertSee('QR Pass Verification')
                ->assertSee('Verify student identity, document requests, and appointments.')
                ->assertSee('ISUFSTPASS SECURE VERIFICATION')
                ->assertSee('Verify QR Pass')
                ->assertSee("Scan a student's QR code or enter their identifier below.", false)
                ->assertSee('Scan QR code, paste QR link, name, or email')
                ->assertSee('Scan QR Code with Camera')
                ->assertSee('USB QR scanners are also supported.', false)
                ->assertSee('Checked in today')
                ->assertSee($office)
                // @js() prints the URL with escaped slashes: \/cici\/qr\/check-in.
                ->assertSee(str_replace('/', '\/', $prefix.'/qr/check-in'), false);
        }
    }

    public function test_a_name_search_verifies_the_student(): void
    {
        $student = $this->makeStudent(['name' => 'Maria Clara Santos']);

        $this->actingAs($this->makeStaff('CICI', 'department'))
            ->get('/cici/qr?q='.urlencode('Maria Clara'))
            ->assertOk()
            ->assertSee('Verified Student')
            ->assertSee('Maria Clara Santos')
            ->assertSee($student->email)
            ->assertDontSee('No Student Found');
    }

    public function test_an_email_search_finds_the_student(): void
    {
        $student = $this->makeStudent(['email' => 'juan.dela.cruz@isufst.edu.ph']);

        $this->actingAs($this->makeStaff('Library', 'library'))
            ->get('/library/qr?q='.urlencode('juan.dela.cruz@isufst.edu.ph'))
            ->assertOk()
            ->assertSee('Verified Student')
            ->assertSee($student->name);
    }

    public function test_a_student_number_search_finds_the_student(): void
    {
        $student = $this->makeStudent();
        $student->studentProfile()->create(['student_id' => '2021-00421']);

        $this->actingAs($this->makeStaff('COED', 'department'))
            ->get('/coed/qr?q='.urlencode('00421'))
            ->assertOk()
            ->assertSee('Verified Student')
            ->assertSee($student->name)
            ->assertSee('2021-00421');
    }

    public function test_an_appointment_pass_is_verified_with_its_booking(): void
    {
        $appointment = $this->makeAppointment($this->makeStudent(), 'SSC');

        $this->actingAs($this->makeStaff('SSC', 'ssc'))
            ->get('/ssc/qr?q='.urlencode($appointment->qr_token))
            ->assertOk()
            ->assertSee('Appointment Found')
            ->assertSee($appointment->user->name)
            ->assertSee('Record Arrival at SSC')
            ->assertDontSee('Not this desk');
    }

    public function test_a_pasted_verification_link_resolves_like_the_bare_token(): void
    {
        $appointment = $this->makeAppointment($this->makeStudent(), 'Accounting');
        $printed = 'https://isuftpass.test/verify/appointment/'.$appointment->qr_token;

        $this->actingAs($this->makeStaff('Accounting', 'accounting'))
            ->get('/accounting/qr?q='.urlencode($printed))
            ->assertOk()
            ->assertSee('Appointment Found')
            ->assertSee('Record Arrival at Accounting');
    }

    public function test_another_offices_pass_is_verified_but_cannot_be_checked_in(): void
    {
        $appointment = $this->makeAppointment($this->makeStudent(), 'Accounting');

        $this->actingAs($this->makeStaff('SSC', 'ssc'))
            ->get('/ssc/qr?q='.urlencode($appointment->qr_token))
            ->assertOk()
            ->assertSee('Appointment Found')
            ->assertSee("Not this desk's booking", false)
            ->assertSee('booked with Accounting')
            ->assertDontSee('Record Arrival at SSC');

        // Reading the page must never move the visit.
        $this->assertSame(AppointmentStatus::CONFIRMED->value, $appointment->fresh()->status);
        $this->assertNull($appointment->fresh()->checked_in_at);
    }

    public function test_a_students_digital_id_verifies_identity_and_todays_visit(): void
    {
        $student = $this->makeStudent();
        $passToken = (string) Str::uuid();
        $student->studentProfile()->create(['pass_token' => $passToken]);
        $appointment = $this->makeAppointment($student, 'Library');

        $this->actingAs($this->makeStaff('Library', 'library'))
            ->get('/library/qr?q='.urlencode($passToken))
            ->assertOk()
            ->assertSee('Verified Student')
            ->assertSee($student->name)
            ->assertSee('Appointment Found')
            ->assertSee('Record Arrival at Library');
    }

    public function test_a_claim_slip_is_verified_and_points_to_the_registrar(): void
    {
        $student = $this->makeStudent();

        $request = $student->documentRequests()->create([
            'student_name' => $student->name,
            'student_address' => 'Iloilo City',
            'student_contact' => '09170000000',
            'student_course_year' => 'BSIT 3',
            'status' => DocumentRequestStatus::READY_FOR_PICKUP->value,
            'purpose_type' => 'employment',
            'educational_status' => 'not_graduated',
            'educational_level' => 'college',
            'claim_mode' => 'personal',
            'submitted_at' => now(),
        ]);

        $claimSlip = route('verify.document', $request->claim_token);

        $this->actingAs($this->makeStaff('Accounting', 'accounting'))
            ->get('/accounting/qr?q='.urlencode($claimSlip))
            ->assertOk()
            ->assertSee('Document Request Found')
            ->assertSee($request->request_number)
            ->assertSee('Released by the Registrar')
            ->assertSee('Ready for Pick-up')
            ->assertDontSee('Record Arrival at Accounting');
    }

    public function test_an_unknown_identifier_finds_nothing(): void
    {
        $this->actingAs($this->makeStaff('CBMSD', 'department'))
            ->get('/cbmsd/qr?q='.urlencode('nobody-matches-this'))
            ->assertOk()
            ->assertSee('No Student Found')
            ->assertDontSee('Verified Student')
            ->assertDontSee('Appointment Found');
    }

    public function test_a_search_lists_only_this_offices_appointments(): void
    {
        $student = $this->makeStudent(['email' => 'queued.student@isufst.edu.ph']);

        $this->makeAppointment($student, 'CICI', [
            'purpose' => 'Consultation about thesis',
            'date' => now()->addDays(2)->toDateString(),
        ]);

        $this->makeAppointment($student, 'SSC', [
            'purpose' => 'Student council concern',
            'date' => now()->addDays(3)->toDateString(),
        ]);

        $this->actingAs($this->makeStaff('CICI', 'department'))
            ->get('/cici/qr?q='.urlencode('queued.student@isufst.edu.ph'))
            ->assertOk()
            ->assertSee('Appointments with CICI')
            ->assertSee('Consultation about thesis')
            ->assertDontSee('Student council concern');
    }

    public function test_the_arrivals_list_is_scoped_to_the_desk(): void
    {
        $ours = $this->makeAppointment($this->makeStudent(['name' => 'Our Arrival']), 'SSC');
        $theirs = $this->makeAppointment($this->makeStudent(['name' => 'Their Arrival']), 'Accounting');

        $this->actingAs($this->makeStaff('Accounting', 'accounting'))
            ->postJson('/accounting/qr/check-in', ['token' => $theirs->qr_token])
            ->assertJsonPath('checkedIn', true);

        $this->actingAs($this->makeStaff('SSC', 'ssc'))
            ->get('/ssc/qr')
            ->assertOk()
            ->assertSee('No check-ins yet today')
            ->assertDontSee('Their Arrival');

        $this->actingAs($this->makeStaff('SSC', 'ssc'))
            ->postJson('/ssc/qr/check-in', ['token' => $ours->qr_token])
            ->assertJsonPath('checkedIn', true);

        $this->actingAs($this->makeStaff('SSC', 'ssc'))
            ->get('/ssc/qr')
            ->assertOk()
            ->assertSee('Our Arrival')
            ->assertDontSee('Their Arrival');
    }

    public function test_the_typed_query_is_escaped_on_the_page(): void
    {
        $this->actingAs($this->makeStaff('CICI', 'department'))
            ->get('/cici/qr?q='.urlencode('<script>alert(1)</script>'))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_students_cannot_open_a_workspace_verification_page(): void
    {
        $this->actingAs($this->makeStudent())
            ->get('/cici/qr')
            ->assertForbidden();
    }
}
