<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every surface that resolves an appointment QR must show THE pass — the
 * student's own Appointment Verification Pass, drawn from the one shared
 * x-appointment-pass-card component.
 *
 * These tests extract the card's banner + body (from the title down to the
 * last verification line) out of each rendered page and compare them as
 * strings: student page vs scanned page, student page vs the registrar's
 * station, student page vs the guard's scanner. If any surface ever forks
 * its own copy of the card — a different reference, scheduled date or
 * time, purpose or status than the student was handed — the extraction no
 * longer matches and this suite fails.
 */
class AppointmentPassParityTest extends TestCase
{
    use RefreshDatabase;

    public function test_scanning_shows_the_students_pass_card_identically(): void
    {
        $owner = $this->student();
        $appointment = $this->appointment($owner, [
            'status' => 'confirmed',
            'notes' => 'Bring the OR number.',
            'rescheduled_at' => now(),
        ]);

        $studentPage = $this->actingAs($owner)
            ->get(route('student.appointments.qr', $appointment))
            ->assertOk()
            ->getContent();

        $scannedPage = $this->get(route('verify.appointment', ['token' => $appointment->qr_token]))
            ->assertOk()
            ->getContent();

        $this->assertSame(
            $this->passCardFrom($studentPage),
            $this->passCardFrom($scannedPage),
            'The scanned page must carry the student\'s pass card exactly.'
        );
    }

    public function test_the_registrar_station_shows_the_students_pass_card_identically(): void
    {
        $owner = $this->student();
        $appointment = $this->appointment($owner, ['status' => 'confirmed']);
        $registrar = User::factory()->create(['role' => 'registrar']);

        $studentPage = $this->actingAs($owner)
            ->get(route('student.appointments.qr', $appointment))
            ->assertOk()
            ->getContent();

        $stationPage = $this->actingAs($registrar)
            ->get(route('registrar.qr.index', [
                'q' => route('verify.appointment', ['token' => $appointment->qr_token]),
            ]))
            ->assertOk()
            ->getContent();

        $this->assertSame(
            $this->passCardFrom($studentPage),
            $this->passCardFrom($stationPage),
            'The registrar\'s station must render the student\'s pass card exactly.'
        );

        $this->assertStringContainsString(
            $this->qrImageFrom($studentPage),
            $stationPage,
            'The station must print the same QR code the student carries.'
        );
    }

    /**
     * The guard's scanner is a fourth surface resolving the same QR, and it
     * shows the office's card rather than a hand-built summary: the guard
     * reads the scheduled time and the status the visit has right now.
     */
    public function test_the_guard_scanner_shows_the_same_pass_card_as_the_offices(): void
    {
        $owner = $this->student();
        $appointment = $this->appointment($owner, [
            'status' => 'confirmed',
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $studentPage = $this->actingAs($owner)
            ->get(route('student.appointments.qr', $appointment))
            ->assertOk()
            ->getContent();

        $guard = User::factory()->create([
            'role' => 'guard',
            'office' => 'Main Gate',
        ]);

        $scan = $this->actingAs($guard)
            ->postJson(route('guard.scan'), ['token' => $appointment->qr_token])
            ->assertOk()
            ->json();

        $this->assertIsString($scan['passHtml'] ?? null, 'The guard scan must return the pass card.');
        $this->assertStringContainsString('Confirmed', $scan['passHtml']);
        $this->assertStringContainsString('Scheduled Time', $scan['passHtml']);
        $this->assertStringContainsString('9:00 AM – 10:00 AM', $scan['passHtml']);

        $this->assertSame(
            $this->passCardFrom($studentPage),
            $this->passCardFrom($scan['passHtml']),
            'The guard scanner must render the office\'s pass card exactly.'
        );
    }

    /**
     * A booking whose hour ran out is closed out before it is shown, and
     * the card the guard reads carries the updated status.
     */
    public function test_the_guard_scanner_shows_a_late_appointment_as_cancelled(): void
    {
        $owner = $this->student();

        $appointment = $this->appointment($owner, [
            'status' => 'confirmed',
            'date' => today()->subDay(),
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $guard = User::factory()->create([
            'role' => 'guard',
            'office' => 'Main Gate',
        ]);

        $scan = $this->actingAs($guard)
            ->postJson(route('guard.scan'), ['token' => $appointment->qr_token])
            ->assertOk()
            ->assertJsonPath('valid', false)
            ->assertJsonPath('pass.statusLabel', 'Cancelled')
            ->json();

        $this->assertStringContainsString('Cancelled', $scan['passHtml']);
        $this->assertStringContainsString('Scheduled Time', $scan['passHtml']);
        $this->assertSame('cancelled', $appointment->fresh()->status);
    }

    public function test_every_surface_prints_the_same_qr_code(): void
    {
        $owner = $this->student();
        $appointment = $this->appointment($owner, ['status' => 'confirmed']);

        $studentPage = $this->actingAs($owner)
            ->get(route('student.appointments.qr', $appointment))
            ->assertOk()
            ->getContent();

        $scannedPage = $this->get(route('verify.appointment', ['token' => $appointment->qr_token]))
            ->assertOk()
            ->getContent();

        $this->assertSame(
            $this->qrImageFrom($studentPage),
            $this->qrImageFrom($scannedPage),
            'Both pages must print one and the same QR code for the booking.'
        );
    }

    /**
     * The spec's body grid, spelled out: the scanned page must carry every
     * field of the pass, including the dynamic status badge.
     */
    public function test_the_scanned_page_shows_every_field_of_the_pass(): void
    {
        $appointment = $this->appointment($this->student(), [
            'office' => 'Cashier',
            'purpose' => 'Tuition fee inquiry',
            'status' => 'confirmed',
        ]);

        $this->get(route('verify.appointment', ['token' => $appointment->qr_token]))
            ->assertOk()
            ->assertSee('Appointment Verification Pass')
            ->assertSee($appointment->office)
            ->assertSee('Reference Number')
            ->assertSee($appointment->reference_code)
            // The pass prints the scheduled visit itself: the date under
            // "Appointment Date" and the office's assigned window under
            // "Scheduled Time", so every surface — including the guard —
            // reads the same arrival time. When the booking was made is
            // still not printed.
            ->assertSee('Appointment Date')
            ->assertSee($appointment->date->format('F j, Y'))
            ->assertSee('Scheduled Time')
            ->assertSee($appointment->scheduledRange())
            ->assertDontSee('Date Booked')
            ->assertDontSee('Time Slot')
            ->assertSee('Concern / Purpose')
            ->assertSee($appointment->purpose)
            ->assertSee($appointment->statusLabel());
    }

    /**
     * The card prints a reference number, so the verify URL accepts that
     * number too — scanning or typing either form opens the same booking.
     */
    public function test_the_verify_route_also_resolves_the_printed_reference_number(): void
    {
        $appointment = $this->appointment($this->student(), ['status' => 'confirmed']);

        $this->get(route('verify.appointment', ['token' => $appointment->reference_code]))
            ->assertOk()
            ->assertSee('Appointment Verification Pass')
            ->assertSee($appointment->reference_code);
    }

    /**
     * The card between its title and the last verification line: blue
     * banner, status badge, office and the whole information grid. Right
     * of it (QR panel, download buttons) each surface may add its own.
     */
    private function passCardFrom(string $html): string
    {
        $start = 'Appointment Verification Pass';
        $end = 'Staff may scan this code to verify your appointment.';

        $from = strpos($html, $start);
        $this->assertNotFalse($from, 'The pass card banner is missing from the page.');

        $to = strpos($html, $end, $from);
        $this->assertNotFalse($to, 'The pass card information grid is missing from the page.');

        return substr($html, $from, $to - $from + strlen($end));
    }

    private function qrImageFrom(string $html): string
    {
        $matched = preg_match(
            '/<img src="(data:image\/svg\+xml;base64,[^"]+)"\s+alt="Appointment QR Code"/',
            $html,
            $matches
        );

        $this->assertSame(
            1,
            $matched,
            'The page must carry the inline QR code of the pass.'
        );

        return $matches[1];
    }

    private function student(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    private function appointment(User $student, array $attributes = []): Appointment
    {
        return $student->appointments()->create([
            'office' => 'Cashier',
            'purpose' => 'Enrollment',
            'date' => today()->addDay(),
            'time_slot' => '09:00 AM - 10:00 AM',
            'status' => 'pending',
            ...$attributes,
        ]);
    }
}
