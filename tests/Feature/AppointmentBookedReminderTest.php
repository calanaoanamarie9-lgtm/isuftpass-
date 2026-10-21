<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentBooked;
use App\Mail\AppointmentReminder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The reminders a student reads about a visit, and the window they quote.
 *
 * Two emails carry them: the confirmation that goes out the moment they
 * press Book appointment, and the one that goes out the day before. Both
 * name the same window and the same penalty, because a student who is told
 * 10-15 minutes in one and 15-30 in the other has no reason to trust
 * either. The window is the one the sweep enforces - AppointmentSweep
 * gives a booking a half hour past its closing time before it cancels it
 * - so the warning describes what the system really does rather than a
 * promise nobody keeps.
 */
class AppointmentBookedReminderTest extends TestCase
{
    use RefreshDatabase;

    private function book(User $student): void
    {
        $this->actingAs($student)
            ->post(route('student.appointments.store'), [
                'office' => 'Registrar',
                'purpose' => 'Enrollment',
                'date' => now()->addDays(3)->toDateString(),
                'time_slot' => '09:00 AM - 10:00 AM',
            ])
            ->assertRedirect(route('student.appointments.index'));
    }

    public function test_the_confirmation_email_says_when_to_come_and_what_a_no_show_costs(): void
    {
        Mail::fake();

        $this->book(User::factory()->create(['role' => 'student']));

        Mail::assertSent(AppointmentBooked::class, function (AppointmentBooked $mail) {
            return $mail->assertSeeInHtml(
                'Come <strong>15&ndash;30 minutes before</strong> your scheduled time.',
                false
            ) && $mail->assertSeeInHtml(
                '<strong>automatic cancellation of your request</strong>',
                false
            );
        });
    }

    /**
     * The window moved from 10-15 minutes to 15-30, and the penalty line is
     * new: a student who remembers the old wording would turn up too early
     * and never learn a missed slot is cancelled for them.
     */
    public function test_the_confirmation_email_no_longer_carries_the_old_reminder(): void
    {
        Mail::fake();

        $this->book(User::factory()->create(['role' => 'student']));

        Mail::assertSent(AppointmentBooked::class, function (AppointmentBooked $mail) {
            return $mail->assertDontSeeInHtml('10&ndash;15 minutes early', false)
                && $mail->assertDontSeeInHtml('Please arrive');
        });
    }

    /**
     * The day-before reminder says the same two things in the same words,
     * so a student reading it the night before is not told anything the
     * confirmation did not tell them.
     */
    public function test_the_day_before_reminder_quotes_the_same_window_and_penalty(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $appointment = $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Enrollment',
            'date' => now()->addDay()->toDateString(),
            'time_slot' => '09:00 AM - 10:00 AM',
            'status' => AppointmentStatus::CONFIRMED->value,
            'confirmed_at' => now(),
        ]);

        $mail = new AppointmentReminder($appointment);

        // Each assertion throws on a miss and hands the mailable back, so
        // they chain - the return value is not a boolean to check.
        $mail->assertSeeInHtml(
            'Come <strong>15&ndash;30 minutes before</strong> your scheduled time.',
            false
        );

        $mail->assertSeeInHtml(
            '<strong>automatic cancellation of your request</strong>',
            false
        );

        $mail->assertDontSeeInHtml('10&ndash;15 minutes early', false);
        $mail->assertDontSeeInHtml('Please arrive');
    }
}
