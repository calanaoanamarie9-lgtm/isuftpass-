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
            ->assertSee('Registrar')
            ->assertSee('Main Gate')
            ->assertSee(today()->format('F j'));

        $this->assertDatabaseHas('gate_logs', [
            'user_id' => $student->id,
            'direction' => 'in',
            'gate' => 'Main Gate',
        ]);

        // Scanning again still records an entry.
        $this->get($this->passUrl($student))->assertOk();

        $this->assertSame(2, GateLog::where('user_id', $student->id)->where('direction', 'in')->count());
    }

    public function test_pass_page_shows_ready_document_requests(): void
    {
        $student = $this->studentWithPass();
        $request = $this->makeRequest($student);

        $this->get($this->passUrl($student))
            ->assertOk()
            ->assertSee($request->request_number);
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
            ->assertSee('Document Claim Slip')
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