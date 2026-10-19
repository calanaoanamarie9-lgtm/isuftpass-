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
 * station. If any of the three ever forks its own copy of the card — a
 * different reference, date, time slot, purpose or status than the student
 * was handed — the extraction no longer matches and this suite fails.
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
            ->assertSee('Appointment Date')
            ->assertSee($appointment->date->format('F j, Y'))
            ->assertSee('Time to Come')
            ->assertSee($appointment->timeToCome())
            ->assertSee('Concern / Purpose')
            ->assertSee($appointment->purpose)
            ->assertSee('Date Booked')
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
