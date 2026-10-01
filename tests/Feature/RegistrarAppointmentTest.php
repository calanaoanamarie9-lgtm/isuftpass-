<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\SlotAvailability;
use App\Models\User;
use App\Notifications\AppointmentRescheduledNotification;
use App\Support\SlotAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrarAppointmentTest extends TestCase
{
    use RefreshDatabase;

    private function makeRegistrar(): User
    {
        return User::factory()->create(['role' => 'registrar']);
    }

    private function makeStudentWithAppointment(string $status = AppointmentStatus::PENDING->value, ?string $date = null): array
    {
        $student = User::factory()->create(['role' => 'student']);
        $appointment = $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Certification',
            'date' => $date ?? now()->addDays(2)->toDateString(),
            'time_slot' => '09:00 AM - 10:00 AM',
            'status' => $status,
        ]);

        return [$student, $appointment];
    }

    private function makeSlotRule(string $date, string $slot, int $capacity, string $status = 'available'): void
    {
        SlotAvailability::create([
            'office_id' => app(SlotAvailabilityService::class)->officeIdFor('Registrar'),
            'date' => $date,
            'time_slot' => $slot,
            'max_capacity' => $capacity,
            'status' => $status,
        ]);
    }

    public function test_reference_code_is_generated_on_create(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $appointment = $user->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Certification',
            'date' => now()->addDays(2)->toDateString(),
            'time_slot' => '09:00 AM - 10:00 AM',
            'status' => 'pending',
        ]);

        $this->assertMatchesRegularExpression('/^APT-' . now()->format('Y') . '-[A-Z0-9]{4}$/', $appointment->reference_code);
        $this->assertNotNull($appointment->refresh()->reference_code);
    }

    public function test_registrar_can_view_management_list(): void
    {
        [$student, $appointment] = $this->makeStudentWithAppointment();

        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/appointments')
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee($appointment->reference_code);
    }

    public function test_registrar_with_office_sees_only_own_office_appointments(): void
    {
        [$student1, $regAppt] = $this->makeStudentWithAppointment();
        $student2 = \App\Models\User::factory()->create(['role' => 'student']);
        $ciciAppt = $student2->appointments()->create([
            'office' => 'CICI',
            'purpose' => 'Academic Consultation',
            'date' => now()->addDays(2)->toDateString(),
            'time_slot' => '10:00 AM - 11:00 AM',
            'status' => AppointmentStatus::PENDING->value,
        ]);

        $ciciUser = User::factory()->create(['role' => 'department', 'office' => 'CICI']);

        $this->actingAs($ciciUser)
            ->get('/registrar/appointments')
            ->assertOk()
            ->assertSee($ciciAppt->reference_code)
            ->assertDontSee($regAppt->reference_code);
    }

    public function test_student_cannot_access_registrar_routes(): void
    {
        [$student] = $this->makeStudentWithAppointment();

        $this->actingAs($student)
            ->get('/registrar/appointments')
            ->assertForbidden();
    }

    public function test_reschedule_updates_record_and_notifies_student(): void
    {
        Mail::fake();

        [$student, $appointment] = $this->makeStudentWithAppointment();
        $newDate = now()->addDays(5)->toDateString();
        $newSlot = '02:00 PM - 03:00 PM';

        $this->actingAs($this->makeRegistrar())
            ->put('/registrar/appointments/' . $appointment->id . '/reschedule', [
                'date' => $newDate,
                'time_slot' => $newSlot,
                'reschedule_reason' => 'Daily limit reached for the day',
            ])
            ->assertRedirect(route('registrar.appointments.show', $appointment));

        $appointment->refresh();

        $this->assertEquals(AppointmentStatus::RESCHEDULED->value, $appointment->status);
        $this->assertEquals(now()->addDays(2)->toDateString(), $appointment->original_date->toDateString());
        $this->assertEquals($newDate, $appointment->date->toDateString());
        $this->assertEquals($newSlot, $appointment->time_slot);
        $this->assertEquals('Daily limit reached for the day', $appointment->reschedule_reason);

        Mail::assertSent(\App\Mail\AppointmentRescheduled::class, function ($mail) use ($student, $appointment) {
            return $mail->hasTo($student->email) && $mail->appointment->is($appointment);
        });

        $this->assertCount(1, $student->notifications);
        $this->assertEquals(AppointmentRescheduledNotification::class, $student->notifications->first()->type);
    }

    public function test_reschedule_rejects_full_slot(): void
    {
        Mail::fake();

        [, $appointment] = $this->makeStudentWithAppointment();

        $fullDate = now()->addDays(5)->toDateString();
        $slot = '09:00 AM - 10:00 AM';

        for ($i = 0; $i < Appointment::SLOT_LIMITS['Registrar']; $i++) {
            $other = User::factory()->create(['role' => 'student']);
            $other->appointments()->create([
                'office' => 'Registrar',
                'purpose' => 'Enrollment',
                'date' => $fullDate,
                'time_slot' => $slot,
                'status' => AppointmentStatus::CONFIRMED->value,
            ]);
        }

        $this->actingAs($this->makeRegistrar())
            ->put('/registrar/appointments/' . $appointment->id . '/reschedule', [
                'date' => $fullDate,
                'time_slot' => $slot,
                'reschedule_reason' => 'Office closure',
            ])
            ->assertSessionHasErrors('time_slot');

        Mail::assertNothingSent();
        $this->assertEquals(AppointmentStatus::PENDING->value, $appointment->refresh()->status);
    }

    public function test_reschedule_requires_reason(): void
    {
        Mail::fake();

        [, $appointment] = $this->makeStudentWithAppointment();

        $this->actingAs($this->makeRegistrar())
            ->put('/registrar/appointments/' . $appointment->id . '/reschedule', [
                'date' => now()->addDays(4)->toDateString(),
                'time_slot' => '01:00 PM - 02:00 PM',
                'reschedule_reason' => '',
            ])
            ->assertSessionHasErrors('reschedule_reason');
    }

    public function test_slots_endpoint_reports_remaining_seats(): void
    {
        [, $appointment] = $this->makeStudentWithAppointment();

        $date = now()->addDays(3)->toDateString();

        $response = $this->actingAs($this->makeRegistrar())
            ->getJson('/registrar/appointments/slots?office=Registrar&date=' . $date)
            ->assertOk()
            ->assertJsonCount(count(Appointment::TIME_SLOTS));

        $response->assertJsonFragment([
            'time' => '09:00 AM - 10:00 AM',
            'remaining' => Appointment::SLOT_LIMITS['Registrar'],
            'is_open' => true,
        ]);
    }

    public function test_rescheduled_appointment_ignores_own_slot_when_checked(): void
    {
        [, $appointment] = $this->makeStudentWithAppointment(AppointmentStatus::RESCHEDULED->value);

        $date = $appointment->date->toDateString();
        $slot = $appointment->time_slot;

        $remainingIgnoring = Appointment::remainingSlots('Registrar', $date, $slot, $appointment->id);
        $remainingIncluding = Appointment::remainingSlots('Registrar', $date, $slot);

        $this->assertGreaterThan($remainingIncluding, $remainingIgnoring);
    }

    public function test_booking_beyond_capacity_is_marked_for_reschedule(): void
    {
        Mail::fake();

        $date = now()->addDays(3)->toDateString();
        $slot = '09:00 AM - 10:00 AM';
        $this->makeSlotRule($date, $slot, 2);

        foreach ([1, 2] as $i) {
            $other = User::factory()->create(['role' => 'student']);
            $other->appointments()->create([
                'office' => 'Registrar',
                'purpose' => 'Enrollment',
                'date' => $date,
                'time_slot' => $slot,
                'status' => AppointmentStatus::CONFIRMED->value,
            ]);
        }

        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->post('/student/appointments', [
                'office' => 'Registrar',
                'purpose' => 'Certification',
                'date' => $date,
                'time_slot' => $slot,
            ])
            ->assertRedirect(route('student.appointments.index'));

        $overflow = Appointment::where('user_id', $student->id)->first();

        $this->assertNotNull($overflow);
        $this->assertEquals(AppointmentStatus::FOR_RESCHEDULE->value, $overflow->status);
        $this->assertEquals('Schedule capacity has been reached.', $overflow->reschedule_reason);

        // Overflow bookings must not consume slot capacity.
        $this->assertSame(0, Appointment::remainingSlots('Registrar', $date, $slot));
    }

    public function test_registrar_cannot_confirm_beyond_capacity(): void
    {
        Mail::fake();

        $date = now()->addDays(3)->toDateString();
        $slot = '09:00 AM - 10:00 AM';
        $this->makeSlotRule($date, $slot, 2);

        foreach ([1, 2] as $i) {
            $other = User::factory()->create(['role' => 'student']);
            $other->appointments()->create([
                'office' => 'Registrar',
                'purpose' => 'Enrollment',
                'date' => $date,
                'time_slot' => $slot,
                'status' => AppointmentStatus::CONFIRMED->value,
            ]);
        }

        [$student, $appointment] = $this->makeStudentWithAppointment(
            AppointmentStatus::PENDING->value,
            $date
        );

        // Force the same full slot for the pending appointment.
        $appointment->update(['time_slot' => $slot]);

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/appointments/'.$appointment->id.'/confirm')
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertEquals(AppointmentStatus::PENDING->value, $appointment->refresh()->status);
    }

    public function test_registrar_can_confirm_pending_appointment(): void
    {
        Mail::fake();

        [, $appointment] = $this->makeStudentWithAppointment();

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/appointments/'.$appointment->id.'/confirm')
            ->assertRedirect()
            ->assertSessionHas('status');

        $appointment->refresh();

        $this->assertEquals(AppointmentStatus::CONFIRMED->value, $appointment->status);
        $this->assertNotNull($appointment->confirmed_at);
    }

    public function test_reschedule_records_original_schedule_and_reason(): void
    {
        Mail::fake();

        [$student, $appointment] = $this->makeStudentWithAppointment();
        $oldDate = $appointment->date->toDateString();
        $newDate = now()->addDays(5)->toDateString();

        $this->actingAs($this->makeRegistrar())
            ->put('/registrar/appointments/'.$appointment->id.'/reschedule', [
                'date' => $newDate,
                'time_slot' => '02:00 PM - 03:00 PM',
                'reschedule_reason' => 'Office advisory',
            ])
            ->assertRedirect(route('registrar.appointments.show', $appointment));

        $appointment->refresh();

        $this->assertEquals($oldDate, $appointment->original_date->toDateString());
        $this->assertEquals('09:00 AM - 10:00 AM', $appointment->original_time_slot);
        $this->assertEquals('02:00 PM - 03:00 PM', $appointment->time_slot);
        $this->assertEquals('Office advisory', $appointment->reschedule_reason);

        Mail::assertSent(\App\Mail\AppointmentRescheduled::class, function ($mail) use ($student) {
            return $mail->hasTo($student->email)
                && str_contains($mail->oldSchedule, '09:00 AM - 10:00 AM');
        });
    }
}