<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\SlotAvailability;
use App\Models\User;
use App\Notifications\AppointmentApprovedNotification;
use App\Support\SlotAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Approval answers with a whole window, not one hour: the day to arrive, the
 * start and the end, and the staff account that set it. The single
 * confirmed_time every pass card and email reads stays the start of that
 * window, so nothing downstream moved.
 */
class RegistrarApproveWindowTest extends TestCase
{
    use RefreshDatabase;

    private function makeRegistrar(): User
    {
        return User::factory()->create(['role' => 'registrar']);
    }

    private function makePending(?string $date = null): Appointment
    {
        $student = User::factory()->create(['role' => 'student']);

        return $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Certification',
            'date' => $date ?? now()->addDays(2)->toDateString(),
            'time_slot' => null,
            'status' => AppointmentStatus::PENDING->value,
        ]);
    }

    public function test_registrar_approves_a_whole_window_and_records_who_set_it(): void
    {
        Notification::fake();

        $appointment = $this->makePending();
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($this->makeRegistrar())
            ->post(route('registrar.appointments.confirm', $appointment), [
                'scheduled_date' => $date,
                'start_time' => '08:30',
                'end_time' => '10:00',
            ])
            ->assertRedirect()
            ->assertSessionHas('status')
            ->assertSessionMissing('errors');

        $appointment->refresh();

        $this->assertSame(AppointmentStatus::CONFIRMED->value, $appointment->status);
        $this->assertSame('08:30', $appointment->start_time);
        $this->assertSame('10:00', $appointment->end_time);
        $this->assertSame($date, $appointment->date->toDateString());

        // The pass card and the approval notice read this one string, so it
        // stays the start of the window.
        $this->assertSame('8:30 AM', $appointment->confirmed_time);
        $this->assertStringContainsString('8:30 AM', $appointment->scheduledRange());
        $this->assertStringContainsString('10:00 AM', $appointment->scheduledRange());

        $this->assertNotNull($appointment->approved_by);

        Notification::assertSentTo($appointment->user, AppointmentApprovedNotification::class);
    }

    public function test_the_window_must_run_forward_onto_a_day_that_has_not_passed(): void
    {
        $appointment = $this->makePending();

        $this->actingAs($this->makeRegistrar())
            ->post(route('registrar.appointments.confirm', $appointment), [
                'scheduled_date' => now()->subDay()->toDateString(),
                'start_time' => '09:00',
                'end_time' => '08:00',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['scheduled_date', 'end_time']);

        $appointment->refresh();

        $this->assertSame(AppointmentStatus::PENDING->value, $appointment->status);
        $this->assertNull($appointment->start_time);
        $this->assertNull($appointment->approved_by);
    }

    public function test_an_approved_window_can_be_moved_again(): void
    {
        $appointment = $this->makePending();
        $appointment->update([
            'status' => AppointmentStatus::CONFIRMED->value,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'confirmed_time' => '9:00 AM',
            'confirmed_at' => now(),
        ]);

        $this->actingAs($this->makeRegistrar())
            ->post(route('registrar.appointments.confirm', $appointment), [
                'scheduled_date' => $appointment->date->toDateString(),
                'start_time' => '13:00',
                'end_time' => '14:30',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $appointment->refresh();

        $this->assertSame('13:00', $appointment->start_time);
        $this->assertSame('14:30', $appointment->end_time);
        $this->assertSame('1:00 PM', $appointment->confirmed_time);
    }

    public function test_the_details_page_renders_the_set_date_and_time_popover(): void
    {
        $appointment = $this->makePending();

        $this->actingAs($this->makeRegistrar())
            ->get(route('registrar.appointments.show', $appointment))
            ->assertOk()
            // One button says what the popover actually does: approve the
            // booking and set the hour the student should come.
            ->assertSee('Approve & schedule')
            ->assertSee('Set date & time')
            // The popover confirms its own submission, so the global
            // "are you sure?" handler has to leave this button alone.
            ->assertSee('data-no-confirm', false)
            ->assertSee('name="scheduled_date"', false)
            ->assertSee('name="start_time"', false)
            ->assertSee('name="end_time"', false);
    }

    public function test_an_approved_appointment_shows_the_green_chip_with_its_window(): void
    {
        $appointment = $this->makePending();
        $appointment->update([
            'status' => AppointmentStatus::CONFIRMED->value,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'confirmed_time' => '9:00 AM',
            'confirmed_at' => now(),
        ]);

        $this->actingAs($this->makeRegistrar())
            ->get(route('registrar.appointments.show', $appointment))
            ->assertOk()
            ->assertSee('Rescheduled:')
            ->assertSee('9:00 AM')
            ->assertSee('Edit');
    }

    public function test_the_legacy_single_time_shape_still_approves(): void
    {
        $appointment = $this->makePending();

        $this->actingAs($this->makeRegistrar())
            ->post(route('registrar.appointments.confirm', $appointment), [
                'confirmed_time' => '15:00',
            ])
            ->assertRedirect()
            ->assertSessionHas('status')
            ->assertSessionMissing('errors');

        $appointment->refresh();

        $this->assertSame(AppointmentStatus::CONFIRMED->value, $appointment->status);
        $this->assertSame('3:00 PM', $appointment->confirmed_time);
        $this->assertSame('15:00', $appointment->start_time);
        $this->assertNull($appointment->end_time);
    }

    /*
    |--------------------------------------------------------------------------
    | The approve popover submits by fetch, so the endpoint has to answer JSON
    | when the browser asks for it - the success alert reads that answer.
    |--------------------------------------------------------------------------
    */

    public function test_a_confirmed_request_gets_a_json_answer_the_popover_can_read(): void
    {
        Notification::fake();

        $appointment = $this->makePending();
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($this->makeRegistrar())
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('registrar.appointments.confirm', $appointment), [
                'scheduled_date' => $date,
                'start_time' => '08:30',
                'end_time' => '10:00',
            ])
            ->assertOk()
            ->assertJsonStructure(['redirect', 'message']);

        $appointment->refresh();

        $this->assertSame(AppointmentStatus::CONFIRMED->value, $appointment->status);
        $this->assertSame('08:30', $appointment->start_time);
        $this->assertSame('10:00', $appointment->end_time);

        Notification::assertSentTo($appointment->user, AppointmentApprovedNotification::class);
    }

    public function test_a_full_slot_comes_back_as_json_so_the_popover_can_say_why(): void
    {
        $date = now()->addDays(3)->toDateString();
        $slot = '09:00 AM - 10:00 AM';

        SlotAvailability::create([
            'office_id' => app(SlotAvailabilityService::class)->officeIdFor('Registrar'),
            'date' => $date,
            'time_slot' => $slot,
            'max_capacity' => 1,
            'status' => 'available',
        ]);

        $other = User::factory()->create(['role' => 'student']);
        $other->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Enrollment',
            'date' => $date,
            'time_slot' => $slot,
            'status' => AppointmentStatus::CONFIRMED->value,
        ]);

        $appointment = $this->makePending($date);
        $appointment->update(['time_slot' => $slot]);

        $this->actingAs($this->makeRegistrar())
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('registrar.appointments.confirm', $appointment), [
                'scheduled_date' => $date,
                'start_time' => '09:00',
                'end_time' => '10:00',
            ])
            ->assertUnprocessable()
            ->assertJsonStructure(['message']);

        $this->assertSame(AppointmentStatus::PENDING->value, $appointment->refresh()->status);
    }
}
