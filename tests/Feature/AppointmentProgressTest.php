<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The timeline that tells a student how far their booking has got.
 *
 * Every appointment draws the same four steps - Requested, Confirmed,
 * Checked in, Completed - the way My Requests draws its own tracking:
 * a vertical timeline whose steps behind the student are solid blue
 * ticks, the step the appointment stands on is that tick with a Current
 * badge beside its name, and the steps ahead are grey circles still
 * carrying their number, joined in the order they are walked. An
 * appointment that left the path (cancelled, or a no show) stops in
 * place with a cross and a line underneath saying what happened,
 * rather than carrying on as if progress were still being made.
 *
 * It is drawn on the student's own QR page, below the actions they get
 * there - nowhere else. The My Appointments list carries the status
 * badge and nothing more, and the pass itself is a shared card: the copy
 * a scanner opens or the registrar's station reads stays exactly as it
 * was.
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
            ->get(route('student.appointments.qr', $booking))
            ->assertOk()
            ->assertSee('Appointment progress')
            ->assertSee('Requested')
            ->assertSee('Checked in')
            ->assertSee('Completed')
            // Requested is where it stands, so it is the ticked step.
            ->assertSee('aria-current="step"', false)
            ->assertSee('bg-[#174b91] text-white', false)
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
            ->get(route('student.appointments.qr', $booking))
            ->assertOk()
            // Requested is behind them: filled, and joined on by a blue line.
            ->assertSee('bg-[#174b91] text-white', false)
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
            ->get(route('student.appointments.qr', $booking))
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
            ->get(route('student.appointments.qr', $booking))
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
            ->get(route('student.appointments.qr', $booking))
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
            ->get(route('student.appointments.qr', $booking))
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
            ->get(route('student.appointments.qr', $booking))
            ->assertOk()
            ->getContent();

        $this->assertSame(4, substr_count($html, 'bg-[#174b91] text-white'));
        $this->assertStringNotContainsString('aria-current="step"', $html);
        // Nothing is outstanding, so no step wears the Current badge.
        $this->assertStringNotContainsString('bg-blue-100 text-blue-700', $html);
        $this->assertStringContainsString(
            $booking->completed_at->format('M j, g:i A'),
            $html
        );
    }

    /**
     * The panel reads the way the tracking on My Requests reads: an
     * eyebrow over the heading, a live dot saying it updates by itself,
     * and four steps that each say in a sentence what they are - with a
     * badge naming the one the appointment stands on.
     */
    public function test_the_timeline_reads_the_way_the_request_tracking_does(): void
    {
        $booking = $this->booking($this->student(), [
            'status' => AppointmentStatus::CHECKED_IN->value,
            'confirmed_at' => now()->subHour(),
            'checked_in_at' => now()->subMinutes(10),
        ]);

        $html = $this->actingAs($booking->user)
            ->get(route('student.appointments.qr', $booking))
            ->assertOk()
            ->assertSee('Tracking live — updates automatically.')
            // The step ahead is still a grey circle carrying its number.
            ->assertSee('bg-white border-2 border-gray-300 text-gray-400', false)
            ->assertSee('bg-blue-100 text-blue-700', false)
            ->assertSee('Your appointment request has been submitted.')
            ->assertSee('The office has approved your schedule and time slot.')
            ->assertSee('Your QR pass was scanned at the main gate.')
            ->assertSee('Your appointment transaction is complete.')
            ->getContent();

        // The Current badge sits on Checked in - the step it stands on -
        // and on nothing else.
        $this->assertSame(1, substr_count($html, 'bg-blue-100 text-blue-700'));
        $this->assertSame(1, substr_count($html, 'aria-current="step"'));
    }

    /**
     * A step that never happened must not claim it did: the timeline
     * crosses it out and says so, leaving the notice below to say why.
     */
    public function test_a_step_that_never_happened_does_not_read_as_happened(): void
    {
        $booking = $this->booking($this->student(), [
            'status' => AppointmentStatus::CANCELLED->value,
            'confirmed_at' => now()->subDay(),
            'cancelled_at' => now()->subHour(),
        ]);

        $this->actingAs($booking->user)
            ->get(route('student.appointments.qr', $booking))
            ->assertOk()
            ->assertSee('This step was never reached.')
            ->assertDontSee('Your QR pass was scanned at the main gate.');
    }

    /**
     * The list carries the status badge and nothing more, so a card stays
     * short enough to scan past - but a booking that is going nowhere
     * still explains itself there, in the banner the list has always had.
     */
    public function test_the_list_carries_no_track_but_still_explains_a_full_slot(): void
    {
        // Booked straight into a full slot, so there is no earlier
        // schedule to point at: the banner is the only place to say it.
        $booking = $this->booking($this->student(), [
            'status' => AppointmentStatus::FOR_RESCHEDULE->value,
        ]);

        $html = $this->actingAs($booking->user)
            ->get(route('student.appointments.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('Appointment progress', $html);
        $this->assertSame(1, substr_count($html, 'This slot is full.'));
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
