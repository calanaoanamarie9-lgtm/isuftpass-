<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\GateLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrScanTest extends TestCase
{
    use RefreshDatabase;

    private function studentWithPass(): User
    {
        $student = User::factory()->create(['role' => 'student']);
        $student->studentProfile()->create([
            'pass_token' => '11111111-2222-3333-4444-555555555555',
        ]);

        return $student;
    }

    private function claimUrl(DocumentRequest $request): string
    {
        return route('verify.document', ['token' => $request->claim_token]);
    }

    private function passUrl(User $student): string
    {
        return route('verify.pass', ['token' => $student->studentProfile->pass_token]);
    }

    private function makeRequest(User $student, string $status = 'ready_for_pickup'): DocumentRequest
    {
        return $student->documentRequests()->create([
            'student_name' => $student->name,
            'student_address' => 'Iloilo City',
            'student_contact' => '09170000000',
            'student_course_year' => 'BSIT 3',
            'status' => $status,
            'purpose_type' => 'employment',
            'educational_status' => 'not_graduated',
            'educational_level' => 'college',
            'claim_mode' => 'personal',
            'submitted_at' => now(),
        ]);
    }

    public function test_scanning_pass_qr_url_opens_verification_page_and_logs_entry(): void
    {
        $student = $this->studentWithPass();
        $appointment = $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Enrollment',
            'date' => today(),
            'time_slot' => '09:00 AM - 10:00 AM',
            'status' => 'confirmed',
        ]);

        $this->get($this->passUrl($student))
            ->assertOk()
            ->assertSee('Valid Student Pass')
            ->assertSee($student->name)
            ->assertSee('Entry recorded')
            ->assertSee('Main Gate')
            ->assertSee(today()->format('F j'))
            // The pass identifies the student; their transactions are not
            // dumped onto the verification page.
            ->assertDontSee('Registrar');

        $this->assertDatabaseHas('gate_logs', [
            'user_id' => $student->id,
            'direction' => 'in',
            'gate' => 'Main Gate',
        ]);

        // Scanning again still records an entry.
        $this->get($this->passUrl($student))->assertOk();

        $this->assertSame(2, GateLog::where('user_id', $student->id)->where('direction', 'in')->count());
    }

    /**
     * The pass QR is the student's identity, not a transaction bundle: a
     * scan must never dump their document requests and appointments.
     */
    public function test_pass_scan_shows_identity_only_without_bulk_transactions(): void
    {
        $student = $this->studentWithPass();
        $request = $this->makeRequest($student);
        $appointment = $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Enrollment',
            'date' => today(),
            'time_slot' => '09:00 AM - 10:00 AM',
            'status' => 'confirmed',
        ]);

        $this->get($this->passUrl($student))
            ->assertOk()
            ->assertSee('Valid Student Pass')
            ->assertDontSee('Active Transactions')
            ->assertDontSee($request->request_number)
            ->assertDontSee($appointment->office);
    }

    public function test_invalid_pass_token_shows_not_found(): void
    {
        $this->get(route('verify.pass', ['token' => '99999999-8888-7777-6666-555555555555']))
            ->assertNotFound()
            ->assertSee('Invalid QR Code');
    }

    public function test_staff_scanning_pass_is_redirected_to_registrar_verification(): void
    {
        $student = $this->studentWithPass();

        $this->actingAs(User::factory()->create(['role' => 'registrar']))
            ->get($this->passUrl($student))
            ->assertRedirect(route('registrar.qr.index', ['q' => $student->studentProfile->pass_token]));

        $this->assertDatabaseHas('gate_logs', ['user_id' => $student->id, 'direction' => 'in']);
    }

    public function test_scanning_claim_qr_url_opens_claim_page(): void
    {
        $student = $this->studentWithPass();
        $request = $this->makeRequest($student);

        $this->get($this->claimUrl($request))
            ->assertOk()
            // The scanned page is the same Digital Claim Pass card the
            // student sees in view details.
            ->assertSee('Digital Claim Pass')
            ->assertSee($request->request_number)
            ->assertSee('Ready for release');

        $other = $this->makeRequest($student, 'submitted');
        $this->get($this->claimUrl($other))
            ->assertOk()
            ->assertSee('not yet ready for release');
    }

    public function test_invalid_claim_token_shows_not_found(): void
    {
        $this->get(route('verify.document', ['token' => '99999999-8888-7777-6666-555555555555']))
            ->assertNotFound()
            ->assertSee('Invalid QR Code');
    }

    /**
     * Every claim QR points at one unique request: the scan must present
     * that request's Digital Claim Pass and none of the student's other
     * requests or appointments.
     */
    public function test_claim_qr_presents_only_that_requests_digital_claim_pass(): void
    {
        $student = $this->studentWithPass();
        $scanned = $this->makeRequest($student, 'ready_for_pickup');
        $other = $this->makeRequest($student, 'submitted');
        $student->appointments()->create([
            'office' => 'Library',
            'purpose' => 'Reference',
            'date' => now()->addDays(3)->toDateString(),
            'time_slot' => '01:00 PM - 02:00 PM',
            'status' => 'confirmed',
        ]);

        $this->get($this->claimUrl($scanned))
            ->assertOk()
            ->assertSee('Digital Claim Pass')
            ->assertSee($scanned->request_number)
            ->assertDontSee($other->request_number)
            ->assertDontSee('Active Transactions')
            ->assertDontSee('Library');
    }

    /**
     * An appointment QR is equally unique: only that one appointment's
     * details are presented, never the student's other transactions.
     */
    public function test_appointment_qr_presents_only_that_appointment(): void
    {
        $student = $this->studentWithPass();
        $scanned = $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Enrollment',
            'date' => now()->addDays(2)->toDateString(),
            'time_slot' => '09:00 AM - 10:00 AM',
            'status' => 'confirmed',
        ]);
        $other = $student->appointments()->create([
            'office' => 'Library',
            'purpose' => 'Reference',
            'date' => now()->addDays(5)->toDateString(),
            'time_slot' => '01:00 PM - 02:00 PM',
            'status' => 'confirmed',
        ]);
        $request = $this->makeRequest($student);

        $this->get(route('verify.appointment', ['token' => $scanned->qr_token]))
            ->assertOk()
            ->assertSee('Registrar')
            ->assertSee($scanned->date->format('M j, Y'))
            ->assertDontSee('Library')
            ->assertDontSee($other->date->format('M j, Y'))
            ->assertDontSee($request->request_number);
    }

    /**
     * The pass page prints exactly one QR per active transaction - there is
     * no student-wide identity QR a scanner could resolve into a bulk list.
     */
    public function test_pass_page_prints_one_qr_per_transaction(): void
    {
        $student = $this->studentWithPass();
        $this->makeRequest($student);
        $this->makeRequest($student, 'submitted');
        $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Enrollment',
            'date' => now()->addDays(2)->toDateString(),
            'time_slot' => '09:00 AM - 10:00 AM',
            'status' => 'confirmed',
        ]);

        $html = $this->actingAs($student)
            ->get(route('student.pass.show'))
            ->assertOk()
            ->getContent();

        $this->assertSame(
            3,
            substr_count($html, 'data:image/svg+xml'),
            'The pass page must print exactly one QR per active transaction.'
        );
    }

    /**
     * The registrar scanner isolates a transaction scan too: the single
     * card for the scanned item, with no student-wide transaction dump
     * underneath it.
     */
    public function test_staff_scanning_a_transaction_qr_sees_only_that_transaction(): void
    {
        $student = $this->studentWithPass();
        $request = $this->makeRequest($student);
        $appointment = $student->appointments()->create([
            'office' => 'Library',
            'purpose' => 'Reference',
            'date' => now()->addDays(3)->toDateString(),
            'time_slot' => '01:00 PM - 02:00 PM',
            'status' => 'confirmed',
        ]);
        $registrar = User::factory()->create(['role' => 'registrar']);

        // Claim QR -> that request's card only.
        $this->actingAs($registrar)
            ->get(route('registrar.qr.index', ['q' => $this->claimUrl($request)]))
            ->assertOk()
            ->assertSee('Document Request Found')
            ->assertSee($request->request_number)
            ->assertDontSee('Verified Student')
            ->assertDontSee('Recent Transactions')
            ->assertDontSee('Library')
            ->assertDontSee('No Student Found');

        // Appointment QR -> that appointment's card only.
        $this->actingAs($registrar)
            ->get(route('registrar.qr.index', ['q' => route('verify.appointment', ['token' => $appointment->qr_token])]))
            ->assertOk()
            ->assertSee('Appointment Found')
            ->assertDontSee('Verified Student')
            ->assertDontSee('Recent Transactions')
            ->assertDontSee('No Student Found');
    }

    /**
     * An identity pass scan shows the student card without a transaction
     * list; a deliberate name search is the one result that still lists
     * recent transactions.
     */
    public function test_staff_identity_scan_shows_no_lists_while_name_search_still_does(): void
    {
        $student = $this->studentWithPass();
        $this->makeRequest($student);
        $registrar = User::factory()->create(['role' => 'registrar']);

        $this->actingAs($registrar)
            ->get(route('registrar.qr.index', ['q' => $this->passUrl($student)]))
            ->assertOk()
            ->assertSee('Verified Student')
            ->assertSee($student->name)
            ->assertDontSee('Recent Transactions')
            ->assertDontSee('No recent document requests.');

        $this->actingAs($registrar)
            ->get(route('registrar.qr.index', ['q' => $student->name]))
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee('Recent Transactions');
    }

    public function test_registrar_verification_accepts_urls_bare_tokens_and_legacy_json(): void
    {
        $student = $this->studentWithPass();
        $token = $student->studentProfile->pass_token;
        $registrar = User::factory()->create(['role' => 'registrar']);

        foreach ([$this->passUrl($student), $token, json_encode(['type' => 'isufstpass', 'token' => $token])] as $q) {
            $this->actingAs($registrar)
                ->get(route('registrar.qr.index', ['q' => $q]))
                ->assertOk()
                ->assertSee($student->name);
        }

        $request = $this->makeRequest($student);

        foreach ([$this->claimUrl($request), json_encode(['type' => 'isufstdoc', 'request' => $request->claim_token])] as $q) {
            $this->actingAs($registrar)
                ->get(route('registrar.qr.index', ['q' => $q]))
                ->assertOk()
                ->assertSee('Document Request Found')
                ->assertSee($request->request_number);
        }

        // Plain name search still works.
        $this->actingAs($registrar)
            ->get(route('registrar.qr.index', ['q' => $student->name]))
            ->assertOk()
            ->assertSee($student->name);
    }

    public function test_registrar_verification_page_renders_camera_scanner(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'registrar']))
            ->get(route('registrar.qr.index'))
            ->assertOk()
            ->assertSee('Scan QR Code with Camera')
            ->assertSee('html5-qrcode');
    }

    public function test_qr_base_url_never_contains_loopback(): void
    {
        $this->studentWithPass();

        $this->get('/login');

        $base = \App\Support\QrUrl::base();

        $this->assertStringNotContainsString('127.0.0.1', $base);
        $this->assertStringNotContainsString('localhost', $base);

        if (config('app.qr_url') === '') {
            $ip = \App\Support\QrUrl::lanIp();
            $this->assertNotNull($ip, 'A LAN IPv4 should be detectable on this machine.');
            $this->assertMatchesRegularExpression('/^http:\/\/\d+\.\d+\.\d+\.\d+(:\d+)?$/', $base);
        }
    }

    public function test_qr_url_helper_builds_verification_links(): void
    {
        $url = \App\Support\QrUrl::to('/verify/pass/abc-123');

        $this->assertStringEndsWith('/verify/pass/abc-123', $url);
        $this->assertMatchesRegularExpression('/^https?:\/\//', $url);
    }
}