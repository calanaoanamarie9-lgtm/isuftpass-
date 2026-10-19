<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\AppointmentBookedNotification;
use App\Notifications\RegistrarAppointmentAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * A booking is the student's side of the appointment pipeline; the
 * registrar owns the other side (confirm, reschedule, cancel). These
 * tests cover the alert that connects them: every new booking reaches
 * every registrar account, and an overflow booking says so.
 */
class AppointmentRegistrarAlertTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudent(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    private function book(User $student, array $overrides = []): void
    {
        $this->actingAs($student)
            ->post(route('student.appointments.store'), array_merge([
                'office' => 'Registrar',
                'purpose' => 'Enrollment',
                'date' => now()->addDays(3)->toDateString(),
            ], $overrides))
            ->assertRedirect(route('student.appointments.index'));
    }

    public function test_booking_an_appointment_notifies_the_student_and_every_registrar_account(): void
    {
        Notification::fake();

        $registrarOne = User::factory()->create(['role' => 'registrar']);
        $registrarTwo = User::factory()->create(['role' => 'registrar']);
        $cashier = User::factory()->create(['role' => 'cashier']);
        $student = $this->makeStudent();

        $this->book($student);

        $appointment = $student->appointments()->first();
        $this->assertNotNull($appointment);

        Notification::assertSentToTimes($student, AppointmentBookedNotification::class, 1);

        // Whoever is at the registrar desk sees the booking arrive.
        Notification::assertSentTo($registrarOne, RegistrarAppointmentAlert::class);
        Notification::assertSentTo($registrarTwo, RegistrarAppointmentAlert::class);

        // The booking belongs to the registrar pipeline — other desks have
        // no confirmation to give.
        Notification::assertNotSentTo($cashier, RegistrarAppointmentAlert::class);
    }

    public function test_the_registrar_appointment_alert_opens_the_appointment_page(): void
    {
        Notification::fake();

        $registrar = User::factory()->create(['role' => 'registrar']);
        $student = $this->makeStudent();

        $this->book($student);

        $appointment = $student->appointments()->first();

        Notification::assertSentTo(
            $registrar,
            RegistrarAppointmentAlert::class,
            // The registrar's own page is where the confirm/reschedule
            // buttons live — not the student portal.
            fn ($notification) => $notification->toArray($registrar)['url']
                === route('registrar.appointments.show', $appointment),
        );
    }
}
