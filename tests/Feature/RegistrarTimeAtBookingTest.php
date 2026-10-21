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
 * approves — so the booking form asks for no hour at all, for any office,
 * and the request arrives with no time_slot.
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

    public function test_a_student_can_book_the_registrar_without_picking_a_time(): void
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
        $this->assertNull($appointment->time_slot);
        $this->assertSame(AppointmentStatus::PENDING->value, $appointment->status);
    }

    public function test_booking_another_office_asks_for_no_time_slot_either(): void
    {
        Mail::fake();
        Notification::fake();

        $student = $this->makeStudent();

        $this->actingAs($student)
            ->post(route('student.appointments.store'), [
                'office' => 'Accounting',
                'purpose' => 'Counseling',
                'date' => now()->addDays(3)->toDateString(),
            ])
            ->assertRedirect(route('student.appointments.index'));

        $appointment = $student->appointments()->firstOrFail();

        // The hour is the office's to give, so the booking waits without one.
        $this->assertNull($appointment->time_slot);
        $this->assertSame(AppointmentStatus::PENDING->value, $appointment->status);
    }

    public function test_the_booking_form_asks_for_no_time_at_all(): void
    {
        $this->actingAs($this->makeStudent())
            ->get(route('student.appointments.create'))
            ->assertOk()
            ->assertDontSee('id="time-section"', false)
            ->assertDontSee('Select Time')
            ->assertDontSee('Available Time Slots')
            ->assertDontSee('Select an available time slot');
    }

    /**
     * The parent and guest forms are the same form with one step more: the
     * child's details sit between the date and the notes.
     */
    public function test_the_parent_form_numbers_its_steps_around_the_removed_time_step(): void
    {
        $parent = User::factory()->create([
            'role' => 'student',
            'registration_type' => 'parent',
        ]);

        $this->actingAs($parent)
            ->get(route('student.appointments.create'))
            ->assertOk()
            ->assertDontSee('Select Time')
            ->assertSee('Student ID');
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
            'time_slot' => null,
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
        // The hour the office gave decides which of its hours the booking
        // sits in, and that is the seat it takes.
        $this->assertSame('09:00 AM - 10:00 AM', $appointment->time_slot);
    }

    public function test_time_to_come_reads_pending_until_the_registrar_answers(): void
    {
        $appointment = $this->makeStudent()->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Certification',
            'date' => now()->addDays(3)->toDateString(),
            'time_slot' => null,
            'status' => AppointmentStatus::PENDING->value,
        ]);

        $this->assertSame('To be set by the office', $appointment->timeToCome());

        $appointment->confirmed_time = '9:00 AM';

        $this->assertSame('9:00 AM', $appointment->timeToCome());
    }

    public function test_a_student_can_update_a_registrar_booking_without_a_time_slot(): void
    {
        Mail::fake();
        Notification::fake();

        $student = $this->makeStudent();
        $appointment = $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Certification',
            'date' => now()->addDays(3)->toDateString(),
            'time_slot' => null,
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
        $this->assertNull($appointment->time_slot);
    }
}
