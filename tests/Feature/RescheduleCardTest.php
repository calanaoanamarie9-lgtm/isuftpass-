<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentRescheduled;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The card the registrar opens to move a booking.
 *
 * It has to answer three questions before the registrar touches anything:
 * which booking this is, what it holds today, and what the office still
 * has open. So the modal carries the reference number, the office, the
 * current day and hour, and a calendar that colours the month the same way
 * the booking form does - green where there is room, red where there is
 * none - with the hours that day still has open listed underneath.
 */
class RescheduleCardTest extends TestCase
{
    use RefreshDatabase;

    private function makeRegistrar(): User
    {
        return User::factory()->create(['role' => 'registrar']);
    }

    private function makeBooking(string $status = AppointmentStatus::PENDING->value): array
    {
        $student = User::factory()->create(['role' => 'student']);

        $appointment = $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Certification',
            'date' => now()->addDays(2)->toDateString(),
            'time_slot' => '09:00 AM - 10:00 AM',
            'status' => $status,
        ]);

        return [$student, $appointment];
    }

    public function test_the_card_names_the_booking_it_moves(): void
    {
        [, $appointment] = $this->makeBooking();

        $this->actingAs($this->makeRegistrar())
            ->get(route('registrar.appointments.index'))
            ->assertOk()
            // The header carries the title and the reference together.
            ->assertSee('Reschedule Appointment')
            ->assertSee($appointment->reference_code)
            // The summary says what the booking holds today.
            ->assertSee('Office')
            ->assertSee('Current date')
            ->assertSee('Current time slot')
            ->assertSee('09:00 AM - 10:00 AM');
    }

    public function test_the_card_offers_a_calendar_and_the_open_hours(): void
    {
        [, $appointment] = $this->makeBooking();

        $this->actingAs($this->makeRegistrar())
            ->get(route('registrar.appointments.index'))
            ->assertOk()
            // The calendar reads the month from the availability endpoint...
            // The endpoints travel inside Alpine's config, where a URL keeps
            // its escaped slashes - so the page carries them that way.
            ->assertSee(str_replace('/', '\/', route('registrar.availability.month')), false)
            // ...and the hours come from the slots endpoint.
            ->assertSee(str_replace('/', '\/', route('registrar.appointments.slots')), false)
            ->assertSee('New date')
            ->assertSee('New time slot')
            ->assertSee('Green days have open slots; red days are full or closed.')
            // The actions the registrar ends on.
            ->assertSee('Confirm Reschedule')
            ->assertSee('Cancel');
    }

    public function test_the_card_is_offered_only_while_the_booking_can_still_move(): void
    {
        $this->actingAs($this->makeRegistrar())
            ->get(route('registrar.appointments.index'))
            ->assertOk()
            ->assertSee('Reschedule');

        foreach ([
            AppointmentStatus::CANCELLED->value,
            AppointmentStatus::COMPLETED->value,
            AppointmentStatus::NO_SHOW->value,
        ] as $status) {
            $this->makeBooking($status);

            $this->actingAs($this->makeRegistrar())
                ->get(route('registrar.appointments.index'))
                ->assertOk()
                ->assertDontSee('Confirm Reschedule');
        }
    }

    /**
     * A booking that is already full cannot be moved into another full slot.
     * The server checks the seats again, whatever the card offered.
     */
    public function test_the_server_refuses_a_slot_that_is_already_full(): void
    {
        Mail::fake();

        [, $appointment] = $this->makeBooking();

        $date = now()->addDays(5)->toDateString();
        $slot = '09:00 AM - 10:00 AM';

        for ($i = 0; $i < Appointment::SLOT_LIMITS['Registrar']; $i++) {
            User::factory()->create(['role' => 'student'])->appointments()->create([
                'office' => 'Registrar',
                'purpose' => 'Certification',
                'date' => $date,
                'time_slot' => $slot,
                'status' => AppointmentStatus::CONFIRMED->value,
            ]);
        }

        $this->actingAs($this->makeRegistrar())
            ->put(route('registrar.appointments.reschedule.update', $appointment), [
                'date' => $date,
                'time_slot' => $slot,
                'reschedule_reason' => 'Office closure',
            ])
            ->assertSessionHasErrors('time_slot');

        Mail::assertNothingSent();
    }

    /**
     * The email that carries the new schedule quotes the same window as the
     * booking and the day-before reminders, and names the penalty for
     * turning up late - or not at all.
     */
    public function test_the_rescheduled_email_quotes_the_window_the_system_enforces(): void
    {
        Mail::fake();

        [$student, $appointment] = $this->makeBooking();

        $this->actingAs($this->makeRegistrar())
            ->put(route('registrar.appointments.reschedule.update', $appointment), [
                'date' => now()->addDays(5)->toDateString(),
                'time_slot' => '10:00 AM - 11:00 AM',
                'reschedule_reason' => 'Office closure',
            ]);

        Mail::assertSent(AppointmentRescheduled::class, function (AppointmentRescheduled $mail) use ($student) {
            return $mail->hasTo($student->email)
                && $mail->assertSeeInHtml(
                    'Come <strong>15&ndash;30 minutes before</strong> your scheduled time',
                    false
                )
                && $mail->assertSeeInHtml(
                    '<strong>automatic cancellation of your request</strong>',
                    false
                );
        });
    }
}
