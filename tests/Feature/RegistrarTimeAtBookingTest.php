<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * A booking is a date, and the office answers with the exact time when it
 * approves. No office asks a student for a time any more, so the booking
 * form carries no time picker and every request arrives with no time_slot.
 */
class RegistrarTimeAtBookingTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudent(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    private function makeRegistrar(): User
    {
        return User::factory()->create(['role' => 'registrar']);
    }

    public function test_a_student_can_book_without_picking_a_time(): void
    {
        Mail::fake();
        Notification::fake();

        $student = $this->makeStudent();

        $this->actingAs($student)
            ->post(route('student.appointments.store'), [
                'office' => 'Registrar',
                'purpose' => 'Certification',
                'date' => now()->addDays(3)->toDateString(),
            ])
            ->assertRedirect(route('student.appointments.index'));

        $appointment = $student->appointments()->firstOrFail();

        $this->assertSame('Registrar', $appointment->office);
        $this->assertSame(AppointmentStatus::PENDING->value, $appointment->status);
    }

    public function test_the_booking_form_carries_no_time_picker(): void
    {
        $this->actingAs($this->makeStudent())
            ->get(route('student.appointments.create'))
            ->assertOk()
            ->assertDontSee('id="time-section"', false)
            ->assertDontSee('name="time_slot"', false);
    }

    public function test_the_registrar_can_approve_a_booking_that_had_no_requested_time(): void
    {
        Mail::fake();
        Notification::fake();

        $student = $this->makeStudent();
        $appointment = $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Certification',
            'date' => now()->addDays(3)->toDateString(),
            'status' => AppointmentStatus::PENDING->value,
        ]);

        $this->actingAs($this->makeRegistrar())
            ->post(route('registrar.appointments.confirm', $appointment), [
                'confirmed_time' => '09:00',
            ])
            ->assertRedirect()
            ->assertSessionMissing('error');

        $appointment->refresh();

        $this->assertSame(AppointmentStatus::CONFIRMED->value, $appointment->status);
        $this->assertSame('9:00 AM', $appointment->confirmed_time);
    }

    public function test_time_to_come_reads_pending_until_the_registrar_answers(): void
    {
        $appointment = $this->makeStudent()->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Certification',
            'date' => now()->addDays(3)->toDateString(),
            'status' => AppointmentStatus::PENDING->value,
        ]);

        $this->assertSame('To be set by the office', $appointment->timeToCome());

        $appointment->confirmed_time = '9:00 AM';

        $this->assertSame('9:00 AM', $appointment->timeToCome());
    }

    public function test_a_student_can_update_a_booking_without_a_time_slot(): void
    {
        Mail::fake();
        Notification::fake();

        $student = $this->makeStudent();
        $appointment = $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Certification',
            'date' => now()->addDays(3)->toDateString(),
            'status' => AppointmentStatus::PENDING->value,
        ]);

        $newDate = now()->addDays(4)->toDateString();

        $this->actingAs($student)
            ->put(route('student.appointments.update', $appointment), [
                'office' => 'Registrar',
                'purpose' => 'Certification',
                'date' => $newDate,
            ])
            ->assertRedirect(route('student.appointments.index'));

        $appointment->refresh();

        $this->assertSame($newDate, $appointment->date->toDateString());
    }
}
