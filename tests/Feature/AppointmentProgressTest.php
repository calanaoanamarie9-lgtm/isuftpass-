<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The track that tells a student how far their booking has got.
 *
 * Every appointment card draws the same four steps - Requested, Confirmed,
 * Checked in, Completed - with the steps behind the student filled, where
 * they stand ringed, and what is ahead grey. An appointment that left the
 * path stops in place with a cross and a line underneath saying what
 * happened, rather than carrying on as if progress were still being made.
 *
 * It is drawn on the My Appointments list and on the student's own QR
 * page, below the actions they get there - never inside the pass itself:
 * the pass is a shared card, and the copy a scanner opens or the
 * registrar's station reads stays exactly as it was.
 */
class AppointmentProgressTest extends TestCase
{
    use RefreshDatabase;

    private function student(): User
    {
        return User::factory()->create(['role' => User::ROLE_STUDENT]);
    }

    private function booking(User $student, array $overrides = []): Appointment
    {
        return $student->appointments()->create(array_merge([
            'office' => 'Registrar',
            'purpose' => 'Enrollment',
            'date' => now()->addDay()->toDateString(),
            'time_slot' => '09:00 AM - 10:00 AM',
            'status' => AppointmentStatus::PENDING->value,
        ], $overrides));
    }

    public function test_a_pending_booking_shows_the_student_where_it_stands(): void
    {
        $booking = $this->booking($this->student());

        $this->actingAs($booking->user)
            ->get(route('student.appointments.index'))
            ->assertOk()
            ->assertSee('Appointment progress')
            ->assertSee('Requested')
            ->assertSee('Checked in')
            ->assertSee('Completed')
            // Requested is where it stands, so it is the ringed step.
            ->assertSee('aria-current="step"', false)
            ->assertSee('ring-4 ring-blue-100', false)
            ->assertSee($booking->created_at->format('M j, g:i A'), false)
            ->assertDontSee('This appointment was cancelled');
    }

    public function test_a_confirmed_booking_reads_as_reached_with_its_timestamp(): void
    {
        $booking = $this->booking($this->student(), [
            'status' => AppointmentStatus::CONFIRMED->value,
            'confirmed_at' => now()->subHour(),
        ]);

        $this->actingAs($booking->user)
            ->get(route('student.appointments.index'))
            ->assertOk()
            // Requested is behind them: filled, and joined on by a blue line.
            ->assertSee('bg-blue-600 border-blue-600 text-white', false)
            ->assertSee('aria-current="step"', false)
            ->assertSee($booking->confirmed_at->format('M j, g:i A'), false);
    }

    public function test_a_checked_in_booking_carries_every_timestamp_reached(): void
    {
        $booking = $this->booking($this->student(), [
            'status' => AppointmentStatus::CHECKED_IN->value,
            'confirmed_at' => now()->subHour(),
            'checked_in_at' => now()->subMinutes(15),
        ]);

        $this->actingAs($booking->user)
            ->get(route('student.appointments.index'))
            ->assertOk()
            ->assertSee($booking->confirmed_at->format('M j, g:i A'), false)
            ->assertSee($booking->checked_in_at->format('M j, g:i A'), false)
            ->assertSee('aria-current="step"', false);
    }

    public function test_a_cancelled_booking_stops_in_place_and_says_when(): void
    {
        $booking = $this->booking($this->student(), [
            'status' => AppointmentStatus::CANCELLED->value,
            'confirmed_at' => now()->subDay(),
            'cancelled_at' => now()->subHour(),
        ]);

        $this->actingAs($booking->user)
            ->get(route('student.appointments.index', ['tab' => 'cancelled']))
            ->assertOk()
            // Confirmed was reached, so the path stops in front of Checked in.
            ->assertSee('border-red-300', false)
            ->assertSee(
                'This appointment was cancelled on '
                .$booking->cancelled_at->format('F j, Y \a\t g:i A').'.'
            );
    }

    public function test_a_booking_never_confirmed_stops_before_the_step_it_needed(): void
    {
        $booking = $this->booking($this->student(), [
            'status' => AppointmentStatus::CANCELLED->value,
            'cancelled_at' => now()->subHour(),
        ]);

        $this->actingAs($booking->user)
            ->get(route('student.appointments.index', ['tab' => 'cancelled']))
            ->assertOk()
            // Nothing was ever confirmed, so it stops in front of Confirmed.
            ->assertSee('border-red-300', false)
            ->assertDontSee('aria-current="step"', false);
    }

    public function test_the_full_slot_line_comes_from_the_track_exactly_once(): void
    {
        $booking = $this->booking($this->student(), [
            'status' => AppointmentStatus::FOR_RESCHEDULE->value,
            'original_date' => now()->addDays(3)->toDateString(),
            'original_time_slot' => '09:00 AM - 10:00 AM',
        ]);

        $html = $this->actingAs($booking->user)
            ->get(route('student.appointments.index'))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, substr_count($html, 'This slot is full.'));
        $this->assertSame(1, substr_count($html, 'border-orange-200 bg-orange-50 text-orange-700'));
    }

    public function test_a_completed_appointment_fills_every_step(): void
    {
        $booking = $this->booking($this->student(), [
            'status' => AppointmentStatus::COMPLETED->value,
            'confirmed_at' => now()->subDays(2),
            'checked_in_at' => now()->subDays(2),
            'completed_at' => now()->subDay(),
        ]);

        $html = $this->actingAs($booking->user)
            ->get(route('student.appointments.index', ['tab' => 'completed']))
            ->assertOk()
            ->getContent();

        $this->assertSame(4, substr_count($html, 'bg-blue-600 border-blue-600 text-white'));
        $this->assertStringNotContainsString('aria-current="step"', $html);
        $this->assertStringContainsString(
            $booking->completed_at->format('M j, g:i A'),
            $html
        );
    }

    public function test_the_qr_page_shows_the_track_below_the_students_own_actions(): void
    {
        // Pending, so the page also carries the student's Edit / Cancel
        // actions - the track has to sit under them, not beside them.
        $booking = $this->booking($this->student(), [
            'confirmed_at' => now()->subHour(),
        ]);

        $html = $this->actingAs($booking->user)
            ->get(route('student.appointments.qr', $booking))
            ->assertOk()
            ->assertSee('Appointment progress')
            ->assertSee('Edit Appointment')
            ->assertSee('Cancel Appointment')
            ->assertSee('aria-current="step"', false)
            ->assertSee($booking->created_at->format('M j, g:i A'), false)
            ->getContent();

        $actions = strpos($html, 'Cancel Appointment');
        $track = strpos($html, 'Appointment progress');

        $this->assertNotFalse($actions);
        $this->assertNotFalse($track);
        $this->assertGreaterThan($actions, $track, 'The track must sit below the student\'s own actions.');

        // The pass above them must not have grown: from its title to the
        // actions, a scanner still reads the card as it always was.
        $from = strpos($html, 'Appointment Verification Pass');

        $this->assertNotFalse($from);
        $this->assertStringNotContainsString(
            'Appointment progress',
            substr($html, $from, $actions - $from)
        );
    }

    /**
     * The pass is one card worn by three surfaces. Only the student's own
     * page carries the track, so a scanner and the registrar reading the
     * same code see exactly what they saw before.
     */
    public function test_the_shared_card_stays_as_it_was_where_staff_and_scanners_read_it(): void
    {
        $booking = $this->booking($this->student(), [
            'status' => AppointmentStatus::CONFIRMED->value,
            'confirmed_at' => now()->subHour(),
        ]);

        $this->get(route('verify.appointment', $booking->qr_token))
            ->assertOk()
            ->assertSee('Appointment Verification Pass')
            ->assertDontSee('Appointment progress');

        $this->actingAs(User::factory()->create(['role' => User::ROLE_REGISTRAR]))
            ->get(route('registrar.qr.index', [
                'q' => route('verify.appointment', ['token' => $booking->qr_token]),
            ]))
            ->assertOk()
            ->assertSee('Appointment Verification Pass')
            ->assertDontSee('Appointment progress');
    }
}
