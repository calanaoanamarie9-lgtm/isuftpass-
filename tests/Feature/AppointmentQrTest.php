<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\GateLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_own_appointment_qr_page(): void
    {
        $student = $this->student();
        $appointment = $this->appointment($student);

        $this->actingAs($student)
            ->get(route('student.appointments.qr', $appointment))
            ->assertOk()
            ->assertSee('Appointment QR')
            ->assertSee($appointment->reference_code)
            ->assertSee('Staff may scan this code to verify your appointment.');
    }

    public function test_student_cannot_view_another_students_appointment_qr(): void
    {
        $owner = $this->student();
        $other = $this->student();

        $this->actingAs($other)
            ->get(route('student.appointments.qr', $this->appointment($owner)))
            ->assertForbidden();
    }

    public function test_scanning_appointment_qr_url_opens_verification_page_and_logs_entry(): void
    {
        $student = $this->student();
        $appointment = $this->appointment($student, ['status' => 'confirmed']);

        $url = route('verify.appointment', ['token' => $appointment->qr_token]);

        $this->get($url)
            ->assertOk()
            ->assertSee('Valid Appointment')
            ->assertSee($student->name)
            ->assertSee('Entry recorded')
            ->assertSee($appointment->reference_code)
            ->assertSee($appointment->office)
            ->assertSee($appointment->date->format('M j, Y'));

        $this->assertDatabaseHas('gate_logs', [
            'user_id' => $student->id,
            'direction' => 'in',
            'gate' => 'Main Gate',
        ]);
    }

    public function test_verification_page_shows_exact_scan_time_not_render_time(): void
    {
        $this->travelTo(now()->setTime(9, 15));

        $student = $this->student();
        $appointment = $this->appointment($student);

        $this->get(route('verify.appointment', ['token' => $appointment->qr_token]))
            ->assertOk()
            ->assertSee('9:15 AM');
    }

    public function test_invalid_appointment_token_shows_not_found(): void
    {
        $this->get(route('verify.appointment', ['token' => '99999999-8888-7777-6666-555555555555']))
            ->assertNotFound()
            ->assertSee('does not match any active appointment');

        $this->assertSame(0, GateLog::count());
    }

    public function test_cancelled_or_completed_appointments_cannot_be_verified(): void
    {
        foreach (['cancelled', 'completed'] as $status) {
            $appointment = $this->appointment($this->student(), ['status' => $status]);

            $this->get(route('verify.appointment', ['token' => $appointment->qr_token]))
                ->assertNotFound();
        }
    }

    public function test_staff_scanning_is_redirected_to_registrar_verification(): void
    {
        $staff = User::factory()->create(['role' => 'registrar']);
        $appointment = $this->appointment($this->student());

        $this->actingAs($staff)
            ->get(route('verify.appointment', ['token' => $appointment->qr_token]))
            ->assertRedirect(route('registrar.qr.index', ['q' => $appointment->qr_token]));

        // The scan is still logged before the redirect.
        $this->assertDatabaseHas('gate_logs', [
            'user_id' => $appointment->user_id,
            'direction' => 'in',
        ]);
    }

    public function test_registrar_scanner_resolves_appointment_qr_url(): void
    {
        $staff = User::factory()->create(['role' => 'registrar']);
        $appointment = $this->appointment($this->student(), ['purpose' => 'Enrollment']);

        $payload = route('verify.appointment', ['token' => $appointment->qr_token]);

        $this->actingAs($staff)
            ->get(route('registrar.qr.index', ['q' => $payload]))
            ->assertOk()
            ->assertSee('Appointment Found')
            ->assertSee($appointment->reference_code)
            ->assertSee('Enrollment')
            ->assertSee($appointment->user->name);
    }

    public function test_registrar_scanner_resolves_bare_appointment_uuid(): void
    {
        $staff = User::factory()->create(['role' => 'registrar']);
        $appointment = $this->appointment($this->student());

        $this->actingAs($staff)
            ->get(route('registrar.qr.index', ['q' => $appointment->qr_token]))
            ->assertOk()
            ->assertSee('Appointment Found')
            ->assertSee($appointment->reference_code);
    }

    public function test_student_can_download_full_appointment_qr_card(): void
    {
        $student = $this->student();
        $student->studentProfile()->create([
            'student_id' => 'ISUFST-2024-0001',
            'course' => 'BS Information Technology',
            'year_level' => '3rd Year',
        ]);
        $appointment = $this->appointment($student);

        $response = $this->actingAs($student)
            ->get(route('student.appointments.qr.download', ['appointment' => $appointment, 'format' => 'svg']));

        $response->assertOk();

        $svg = $response->getContent();
        $disposition = $response->headers->get('Content-Disposition');

        $this->assertStringContainsString('attachment', $disposition);
        $this->assertStringContainsString('.svg"', $disposition);
        $this->assertStringContainsString('image/svg+xml', $response->headers->get('Content-Type'));
        // The whole card must be included, not just the bare QR code.
        $this->assertStringContainsString('ISUFSTPASS', $svg);
        $this->assertStringContainsString('DIGITAL STUDENT ID', $svg);
        $this->assertStringContainsString($student->name, $svg);
        $this->assertStringContainsString('ISUFST-2024-0001', $svg);
        $this->assertStringContainsString('BS Information Technology', $svg);
        $this->assertStringContainsString('3rd Year', $svg);
        $this->assertStringContainsString('APPOINTMENT QR', $svg);
        $this->assertStringContainsString($appointment->reference_code, $svg);
        $this->assertStringContainsString('ISUFSTPASS VERIFIED', $svg);
    }

    /**
     * The bare download — the URL every button in the UI points at — must be
     * a PNG, because a phone cannot open the SVG this used to serve.
     * Android downloads image/svg+xml, finds nothing that claims it, and
     * reports "Couldn't open file".
     */
    public function test_appointment_qr_download_defaults_to_png_a_phone_can_open(): void
    {
        $student = $this->student();
        $student->studentProfile()->create([
            'student_id' => 'ISUFST-2024-0001',
            'course' => 'BS Information Technology',
            'year_level' => '3rd Year',
        ]);
        $appointment = $this->appointment($student);

        $response = $this->actingAs($student)
            ->get(route('student.appointments.qr.download', $appointment));

        $response->assertOk();

        $png = $response->getContent();

        $this->assertSame('image/png', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('.png"', $response->headers->get('Content-Disposition'));

        // Real PNG magic bytes — the header a phone's gallery reads to
        // decide it can open the file at all.
        $this->assertSame("\x89PNG\r\n\x1a\n", substr($png, 0, 8));
        $this->assertSame((string) strlen($png), $response->headers->get('Content-Length'));

        $image = @imagecreatefromstring($png);
        $this->assertNotFalse($image, 'The download must decode as an image.');
        $this->assertSame(640, imagesx($image));
        $this->assertSame(1010, imagesy($image));
    }

    private function student(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    private function appointment(User $student, array $attributes = []): Appointment
    {
        return $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Request document',
            'date' => today()->addDay(),
            'time_slot' => '09:00 AM - 10:00 AM',
            'status' => 'pending',
            ...$attributes,
        ]);
    }
}
