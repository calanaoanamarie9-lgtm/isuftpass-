<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentRescheduled;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The modal that moves a booking to another day.
 *
 * It has to answer three questions before the registrar touches anything:
 * which booking this is, what it holds today, and which day it could move
 * to. So the modal carries the reference number, the student, the office,
 * the current day and hour, and a calendar that colours the month the same
 * way the booking form does - green where the office still has room, red
 * where it does not.
 *
 * There is one way in: the green status chip on the appointment record is
 * the trigger. The card brings no button of its own, so a booking is moved
 * from the one place that says what it holds.
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

    /**
     * The card is drawn on its own here, so the component is tested as the
     * piece it is.
     */
    private function renderCard(Appointment $appointment): string
    {
        return Blade::render(
            '<x-reschedule-card :appointment="$appointment"'
            .' month-url="/month" submit-url="/submit" />',
            ['appointment' => $appointment],
        );
    }

    public function test_the_card_names_the_booking_it_moves(): void
    {
        [, $appointment] = $this->makeBooking();

        $html = $this->renderCard($appointment);

        // The header carries the title and the reference together.
        $this->assertStringContainsString('Reschedule Appointment', $html);
        $this->assertStringContainsString($appointment->reference_code, $html);
        // The details say what the booking holds today.
        $this->assertStringContainsString('Student name', $html);
        $this->assertStringContainsString('Office', $html);
        $this->assertStringContainsString('Current date', $html);
        $this->assertStringContainsString('Current time slot', $html);
        $this->assertStringContainsString('09:00 AM - 10:00 AM', $html);
    }

    public function test_the_card_asks_for_a_day_and_a_reason(): void
    {
        [, $appointment] = $this->makeBooking();

        $html = $this->renderCard($appointment);

        // The calendar reads the month from the availability endpoint.
        $this->assertStringContainsString('/month', $html);
        $this->assertStringContainsString('New date', $html);
        // The reason, and the choice of it.
        $this->assertStringContainsString('Reason for rescheduling', $html);
        $this->assertStringContainsString('Choose a reason', $html);
        // The actions the registrar ends on.
        $this->assertStringContainsString('Confirm Reschedule', $html);
        $this->assertStringContainsString('Cancel', $html);
        // The hour is not the card's to ask about: the booking keeps the one
        // it holds, or waits for the office to set one.
        $this->assertStringNotContainsString('New time slot', $html);
    }

    /**
     * The green chip is the reschedule button: it opens the modal, and the
     * modal is the only reschedule control on the record - there is no
     * second button beside it.
     */
    public function test_the_status_badge_opens_the_modal_and_there_is_no_other_button(): void
    {
        [, $appointment] = $this->makeBooking(AppointmentStatus::CONFIRMED->value);

        $html = $this->actingAs($this->makeRegistrar())
            ->get(route('registrar.appointments.show', $appointment))
            ->assertOk()
            ->getContent();

        // The chip announces the booking it means.
        $this->assertStringContainsString(
            "\$dispatch('open-reschedule', { id: ".$appointment->id.' })',
            $html,
        );
        // The modal answers that announcement, and only that booking's.
        $this->assertStringContainsString('@open-reschedule.window', $html);
        $this->assertSame(1, substr_count($html, 'x-data="rescheduleCard('));
        // The modal carries what the student and the office need to see.
        $this->assertSame(1, substr_count($html, 'Reschedule Appointment'));
        $this->assertStringContainsString($appointment->reference_code, $html);
        $this->assertStringContainsString('Choose a reason', $html);
        $this->assertStringContainsString('New date', $html);
    }

    /**
     * The card brings no button of its own: the chip is the trigger, so
     * there is one way in and not two.
     */
    public function test_the_card_brings_no_button_of_its_own(): void
    {
        [, $appointment] = $this->makeBooking(AppointmentStatus::CONFIRMED->value);

        $html = $this->actingAs($this->makeRegistrar())
            ->get(route('registrar.appointments.show', $appointment))
            ->assertOk()
            ->getContent();

        // The trigger button of the card is not on the page; the chip is.
        $this->assertStringNotContainsString(":aria-expanded=\"open ? 'true' : 'false'\"", $html);
        $this->assertStringContainsString('aria-haspopup="dialog"', $html);
    }

    /**
     * The management list is for scanning, so it carries no modal.
     */
    public function test_the_management_list_carries_no_modal(): void
    {
        $this->actingAs($this->makeRegistrar())
            ->get(route('registrar.appointments.index'))
            ->assertOk()
            ->assertDontSee('x-data="rescheduleCard(', false);
    }

    /**
     * A booking that moves day keeps the hour it held: the card asks for a
     * day, and the office's own hour travels with the booking.
     */
    public function test_a_day_only_reschedule_keeps_the_hour_the_booking_held(): void
    {
        Mail::fake();

        [, $appointment] = $this->makeBooking();

        $date = now()->addDays(5)->toDateString();

        $this->actingAs($this->makeRegistrar())
            ->put(route('registrar.appointments.reschedule.update', $appointment), [
                'date' => $date,
                'reschedule_reason' => 'Office closure',
            ])
            ->assertRedirect();

        $appointment->refresh();

        $this->assertEquals(AppointmentStatus::RESCHEDULED->value, $appointment->status);
        $this->assertEquals($date, $appointment->date->toDateString());
        $this->assertEquals('09:00 AM - 10:00 AM', $appointment->time_slot);
        $this->assertEquals(now()->addDays(2)->toDateString(), $appointment->original_date->toDateString());
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
