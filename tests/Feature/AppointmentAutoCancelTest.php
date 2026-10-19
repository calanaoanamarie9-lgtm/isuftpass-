<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentCancelledNotification;
use App\Support\AppointmentSweep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * A booking the student never arrived for.
 *
 * Three things have to hold: the booking closes itself out once its hour is
 * gone and not a minute sooner (a student inside the window, inside the
 * grace, or already checked in is never touched), the gate reads such a pass
 * as cancelled the instant it is scanned, and the endpoint that does the
 * sweeping for everyone else stays behind the cron secret.
 *
 * Appointments run on campus wall clock, so every test pins "now" rather
 * than reading it - 06:00 UTC is 2:00 PM in Manila.
 */
class AppointmentAutoCancelTest extends TestCase
{
    use RefreshDatabase;

    /** 06:00 UTC == 2:00 PM Manila: a 9:00 - 10:00 AM window is long gone. */
    private const AFTER_HOURS = '2026-10-19 06:00:00';

    /** 01:00 UTC == 9:00 AM Manila: the window is still open. */
    private const INSIDE_WINDOW = '2026-10-19 01:00:00';

    /** 02:15 UTC == 10:15 AM Manila: late, but still inside the grace. */
    private const INSIDE_GRACE = '2026-10-19 02:15:00';

    private function at(string $utc): void
    {
        $this->travelTo(Carbon::parse($utc, 'UTC'));
    }

    private function student(): User
    {
        return User::factory()->create(['role' => User::ROLE_STUDENT]);
    }

    private function booking(User $student, array $overrides = []): Appointment
    {
        return $student->appointments()->create(array_merge([
            'office' => 'Registrar',
            'purpose' => 'Enrollment',
            'date' => '2026-10-19',
            'time_slot' => '09:00 AM - 10:00 AM',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'status' => AppointmentStatus::CONFIRMED->value,
        ], $overrides));
    }

    /*
    |--------------------------------------------------------------------------
    | The sweep
    |--------------------------------------------------------------------------
    */

    public function test_a_booking_whose_window_closed_without_an_arrival_is_cancelled(): void
    {
        $this->at(self::AFTER_HOURS);
        $appointment = $this->booking($this->student());

        $this->assertSame(1, AppointmentSweep::run());

        $fresh = $appointment->fresh();

        $this->assertSame(AppointmentStatus::CANCELLED->value, $fresh->status);
        $this->assertNotNull($fresh->cancelled_at);
    }

    public function test_the_student_is_told_their_booking_was_cancelled(): void
    {
        Notification::fake();

        $this->at(self::AFTER_HOURS);
        $student = $this->student();
        $this->booking($student);

        AppointmentSweep::run();

        Notification::assertSentTo($student, AppointmentCancelledNotification::class);
    }

    public function test_a_booking_inside_its_window_is_left_alone(): void
    {
        $this->at(self::INSIDE_WINDOW);
        $appointment = $this->booking($this->student());

        $this->assertSame(0, AppointmentSweep::run());
        $this->assertSame(AppointmentStatus::CONFIRMED->value, $appointment->fresh()->status);
    }

    public function test_a_student_inside_the_grace_period_still_gets_in(): void
    {
        $this->at(self::INSIDE_GRACE);
        $appointment = $this->booking($this->student());

        $this->assertSame(0, AppointmentSweep::run());
        $this->assertSame(AppointmentStatus::CONFIRMED->value, $appointment->fresh()->status);
        $this->assertNull($appointment->fresh()->cancelled_at);
    }

    public function test_a_student_who_already_arrived_is_never_cancelled(): void
    {
        $this->at(self::AFTER_HOURS);
        $appointment = $this->booking($this->student(), [
            'status' => AppointmentStatus::CHECKED_IN->value,
            'checked_in_at' => now(),
        ]);

        $this->assertSame(0, AppointmentSweep::run());
        $this->assertSame(AppointmentStatus::CHECKED_IN->value, $appointment->fresh()->status);
    }

    public function test_a_pending_request_is_alive_until_its_day_is_over(): void
    {
        $request = $this->booking($this->student(), [
            'date' => '2026-10-19',
            'start_time' => null,
            'end_time' => null,
            'time_slot' => null,
            'status' => AppointmentStatus::PENDING->value,
        ]);

        // Still the office's to answer, all day long.
        $this->at(self::AFTER_HOURS);
        $this->assertSame(0, AppointmentSweep::run());
        $this->assertSame(AppointmentStatus::PENDING->value, $request->fresh()->status);

        // The day it asked for is over and it was never approved.
        $this->at('2026-10-20 06:00:00');
        $this->assertSame(1, AppointmentSweep::run());
        $this->assertSame(AppointmentStatus::CANCELLED->value, $request->fresh()->status);
    }

    public function test_visits_that_are_already_finished_are_left_alone(): void
    {
        $this->at('2026-10-21 06:00:00');

        $completed = $this->booking($this->student(), [
            'status' => AppointmentStatus::COMPLETED->value,
        ]);
        $awaitingReschedule = $this->booking($this->student(), [
            'status' => AppointmentStatus::FOR_RESCHEDULE->value,
        ]);

        $this->assertSame(0, AppointmentSweep::run());
        $this->assertSame(AppointmentStatus::COMPLETED->value, $completed->fresh()->status);
        $this->assertSame(
            AppointmentStatus::FOR_RESCHEDULE->value,
            $awaitingReschedule->fresh()->status
        );
    }

    /*
    |--------------------------------------------------------------------------
    | The guard's scanner
    |--------------------------------------------------------------------------
    */

    public function test_a_guard_scan_cancels_a_missed_booking_and_reports_it_as_cancelled(): void
    {
        $this->at(self::AFTER_HOURS);
        $appointment = $this->booking($this->student());

        $guard = User::factory()->create([
            'role' => User::ROLE_GUARD,
            'office' => 'Main Gate',
        ]);

        $this->actingAs($guard)
            ->postJson('/guard/scan', ['token' => $appointment->qr_token])
            ->assertOk()
            ->assertJsonPath('valid', false)
            ->assertJsonPath('pass.statusLabel', 'Cancelled')
            ->assertJsonPath('pass.statusTone', 'invalid')
            ->assertJsonPath(
                'message',
                'This appointment is “Cancelled” and cannot be used for entry.'
            );

        $this->assertSame(AppointmentStatus::CANCELLED->value, $appointment->fresh()->status);
    }

    public function test_a_guard_scan_leaves_a_booking_inside_its_window_untouched(): void
    {
        $this->at(self::INSIDE_WINDOW);
        $appointment = $this->booking($this->student());

        $guard = User::factory()->create([
            'role' => User::ROLE_GUARD,
            'office' => 'Main Gate',
        ]);

        $this->actingAs($guard)
            ->postJson('/guard/scan', ['token' => $appointment->qr_token])
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('pass.statusLabel', 'Confirmed')
            ->assertJsonPath('pass.statusTone', 'valid');

        $this->assertSame(AppointmentStatus::CONFIRMED->value, $appointment->fresh()->status);
    }

    /*
    |--------------------------------------------------------------------------
    | The office desk's scanner
    |--------------------------------------------------------------------------
    */

    public function test_a_missed_booking_is_reported_at_the_desk_instead_of_being_walked_in(): void
    {
        $this->at(self::AFTER_HOURS);
        $appointment = $this->booking($this->student(), ['office' => 'COAG']);

        $desk = User::factory()->create(['role' => 'department', 'office' => 'COAG']);

        $this->actingAs($desk)
            ->postJson('/coag/qr/check-in', ['token' => $appointment->qr_token])
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('checkedIn', false)
            ->assertJsonPath('status', 'Cancelled')
            ->assertJsonPath('message', 'Cannot check in — this visit is Cancelled.');

        $fresh = $appointment->fresh();

        $this->assertSame(AppointmentStatus::CANCELLED->value, $fresh->status);
        $this->assertNull($fresh->checked_in_at);
    }

    /*
    |--------------------------------------------------------------------------
    | The scheduled endpoint
    |--------------------------------------------------------------------------
    */

    public function test_the_auto_cancel_endpoint_needs_the_cron_secret(): void
    {
        Config::set('app.cron_secret', 'sweep-secret');

        $this->at(self::AFTER_HOURS);
        $appointment = $this->booking($this->student());

        $this->getJson('/cron/auto-cancel')->assertForbidden();
        $this->withHeaders(['X-Cron-Secret' => 'wrong'])
            ->getJson('/cron/auto-cancel')
            ->assertForbidden();

        $this->assertSame(AppointmentStatus::CONFIRMED->value, $appointment->fresh()->status);
    }

    public function test_the_auto_cancel_endpoint_cancels_the_missed_bookings(): void
    {
        Config::set('app.cron_secret', 'sweep-secret');

        $this->at(self::AFTER_HOURS);
        $appointment = $this->booking($this->student());

        $this->withHeaders(['X-Cron-Secret' => 'sweep-secret'])
            ->getJson('/cron/auto-cancel')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('cancelled', 1);

        $this->assertSame(AppointmentStatus::CANCELLED->value, $appointment->fresh()->status);
    }

    /**
     * Both endpoints read their secret through the same guard, so the one
     * that already ran in production has to stay exactly as locked as the
     * new one.
     */
    public function test_the_reminders_endpoint_shares_the_same_locked_door(): void
    {
        Config::set('app.cron_secret', 'sweep-secret');

        $this->getJson('/cron/reminders')->assertForbidden();

        $this->withHeaders(['X-Cron-Secret' => 'wrong'])
            ->getJson('/cron/reminders')
            ->assertForbidden();

        $this->withHeaders(['X-Cron-Secret' => 'sweep-secret'])
            ->getJson('/cron/reminders')
            ->assertOk()
            ->assertJsonPath('ok', true);
    }
}
