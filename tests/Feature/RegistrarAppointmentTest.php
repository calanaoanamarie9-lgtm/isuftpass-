<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentApproved;
use App\Mail\AppointmentRescheduled;
use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentApprovedNotification;
use App\Notifications\AppointmentRescheduledNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
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
            'status' => $status,
        ]);

        return [$student, $appointment];
    }

    public function test_reference_code_is_generated_on_create(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $appointment = $user->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Certification',
            'date' => now()->addDays(2)->toDateString(),
            'status' => 'pending',
        ]);

        $this->assertMatchesRegularExpression('/^APT-'.now()->format('Y').'-[A-Z0-9]{4}$/', $appointment->reference_code);
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

    public function test_the_details_page_no_longer_shows_the_current_schedule_panel(): void
    {
        [, $appointment] = $this->makeStudentWithAppointment();

        $this->actingAs($this->makeRegistrar())
            ->get(route('registrar.appointments.show', $appointment))
            ->assertOk()
            ->assertDontSee('Confirmed appointment information')
            ->assertDontSee('Appointment Date')
            ->assertDontSee('Time Slot Asked');
    }

    public function test_the_details_page_no_longer_shows_the_verification_panel(): void
    {
        [, $appointment] = $this->makeStudentWithAppointment();

        $this->actingAs($this->makeRegistrar())
            ->get(route('registrar.appointments.show', $appointment))
            ->assertOk()
            ->assertDontSee('ISUFSTPASS Verified')
            ->assertDontSee('Secure university transaction')
            ->assertDontSee('Appointment record is securely stored');
    }

    public function test_registrar_with_office_sees_only_own_office_appointments(): void
    {
        [$student1, $regAppt] = $this->makeStudentWithAppointment();
        $student2 = User::factory()->create(['role' => 'student']);
        $ciciAppt = $student2->appointments()->create([
            'office' => 'CICI',
            'purpose' => 'Academic Consultation',
            'date' => now()->addDays(2)->toDateString(),
            'status' => AppointmentStatus::PENDING->value,
        ]);

        $ciciUser = User::factory()->create(['role' => 'department', 'office' => 'CICI']);

        $this->actingAs($ciciUser)
            ->get('/registrar/appointments')
            ->assertOk()
            ->assertSee($ciciAppt->reference_code)
            ->assertDontSee($regAppt->reference_code);
    }

    public function test_registrar_can_filter_the_list_by_status(): void
    {
        $registrar = $this->makeRegistrar();

        // "Approved" is the page's word for a stored "confirmed" booking.
        $appointments = [
            'pending' => $this->makeStudentWithAppointment(AppointmentStatus::PENDING->value)[1],
            'approved' => $this->makeStudentWithAppointment(AppointmentStatus::CONFIRMED->value)[1],
            'rescheduled' => $this->makeStudentWithAppointment(AppointmentStatus::RESCHEDULED->value)[1],
            'cancelled' => $this->makeStudentWithAppointment(AppointmentStatus::CANCELLED->value)[1],
        ];

        foreach ($appointments as $filter => $shown) {
            $response = $this->actingAs($registrar)
                ->get('/registrar/appointments?status='.$filter)
                ->assertOk()
                ->assertSee($shown->reference_code);

            foreach ($appointments as $other => $hidden) {
                if ($other !== $filter) {
                    $response->assertDontSee($hidden->reference_code);
                }
            }
        }

        // "All Status" keeps every booking on the page.
        $response = $this->actingAs($registrar)
            ->get('/registrar/appointments?status=all')
            ->assertOk();

        foreach ($appointments as $appointment) {
            $response->assertSee($appointment->reference_code);
        }
    }

    public function test_registrar_can_filter_the_list_by_user_type(): void
    {
        $registrar = $this->makeRegistrar();

        $makeAppointment = function (User $user): Appointment {
            return $user->appointments()->create([
                'office' => 'Registrar',
                'purpose' => 'Certification',
                'date' => now()->addDays(2)->toDateString(),
                'status' => AppointmentStatus::PENDING->value,
            ]);
        };

        // A plain student (registration_type is left null for them)...
        $student = User::factory()->create(['role' => 'student']);
        $studentAppt = $makeAppointment($student);

        // ...an alumnus...
        $alumni = User::factory()->create(['role' => 'student', 'registration_type' => 'alumni']);
        $alumniAppt = $makeAppointment($alumni);

        // ...and a legacy "guardian" parent.
        $parent = User::factory()->create(['role' => 'student', 'registration_type' => 'guardian']);
        $parentAppt = $makeAppointment($parent);

        // Alumni filter shows only the alumnus.
        $this->actingAs($registrar)
            ->get('/registrar/appointments?user_type=alumni')
            ->assertOk()
            ->assertSee($alumniAppt->reference_code)
            ->assertDontSee($studentAppt->reference_code)
            ->assertDontSee($parentAppt->reference_code);

        // "Parent" also covers the legacy 'guardian' registration_type.
        $this->actingAs($registrar)
            ->get('/registrar/appointments?user_type=parent')
            ->assertOk()
            ->assertSee($parentAppt->reference_code)
            ->assertDontSee($alumniAppt->reference_code)
            ->assertDontSee($studentAppt->reference_code);

        // "Student" is every account with no registration_type.
        $this->actingAs($registrar)
            ->get('/registrar/appointments?user_type=student')
            ->assertOk()
            ->assertSee($studentAppt->reference_code)
            ->assertDontSee($alumniAppt->reference_code)
            ->assertDontSee($parentAppt->reference_code);
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

        $this->actingAs($this->makeRegistrar())
            ->put('/registrar/appointments/'.$appointment->id.'/reschedule', [
                'date' => $newDate,
                'reschedule_reason' => 'The office cannot accommodate the requested day',
            ])
            ->assertRedirect(route('registrar.appointments.show', $appointment));

        $appointment->refresh();

        $this->assertEquals(AppointmentStatus::RESCHEDULED->value, $appointment->status);
        $this->assertEquals(now()->addDays(2)->toDateString(), $appointment->original_date->toDateString());
        $this->assertEquals($newDate, $appointment->date->toDateString());
        $this->assertEquals('The office cannot accommodate the requested day', $appointment->reschedule_reason);

        // A moved booking waits for a fresh answer: the old window is void.
        $this->assertNull($appointment->confirmed_time);
        $this->assertNull($appointment->confirmed_at);
        $this->assertNull($appointment->start_time);
        $this->assertNull($appointment->end_time);

        Mail::assertSent(AppointmentRescheduled::class, function ($mail) use ($student, $appointment) {
            return $mail->hasTo($student->email) && $mail->appointment->is($appointment);
        });

        $this->assertCount(1, $student->notifications);
        $this->assertEquals(AppointmentRescheduledNotification::class, $student->notifications->first()->type);
    }

    public function test_reschedule_requires_reason(): void
    {
        Mail::fake();

        [, $appointment] = $this->makeStudentWithAppointment();

        $this->actingAs($this->makeRegistrar())
            ->put('/registrar/appointments/'.$appointment->id.'/reschedule', [
                'date' => now()->addDays(4)->toDateString(),
                'reschedule_reason' => '',
            ])
            ->assertSessionHasErrors('reschedule_reason');
    }

    public function test_registrar_can_confirm_pending_appointment_and_sets_the_time_to_come(): void
    {
        Mail::fake();
        Notification::fake();

        [, $appointment] = $this->makeStudentWithAppointment();

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/appointments/'.$appointment->id.'/confirm', [
                'confirmed_time' => '14:30',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $appointment->refresh();

        $this->assertEquals(AppointmentStatus::CONFIRMED->value, $appointment->status);
        $this->assertNotNull($appointment->confirmed_at);

        // The office answers the booking with the time it wants the student
        // there — a booking names a day, not an hour.
        $this->assertSame('2:30 PM', $appointment->confirmed_time);
        $this->assertSame('2:30 PM', $appointment->timeToCome());

        // And the student is actually told.
        Notification::assertSentTo(
            $appointment->user,
            AppointmentApprovedNotification::class,
            fn (AppointmentApprovedNotification $notification) => str_contains(
                $notification->toArray($appointment->user)['message'],
                'Come at 2:30 PM'
            )
        );
    }

    public function test_approving_without_a_time_is_refused(): void
    {
        Mail::fake();

        [, $appointment] = $this->makeStudentWithAppointment();

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/appointments/'.$appointment->id.'/confirm')
            ->assertRedirect()
            ->assertSessionHasErrors('confirmed_time');

        $this->assertEquals(AppointmentStatus::PENDING->value, $appointment->refresh()->status);
    }

    public function test_the_time_must_fall_inside_the_offices_own_hours(): void
    {
        Mail::fake();

        [, $appointment] = $this->makeStudentWithAppointment();

        // Before the office opens.
        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/appointments/'.$appointment->id.'/confirm', [
                'confirmed_time' => '06:00',
            ])
            ->assertSessionHasErrors('confirmed_time');

        // After it closes.
        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/appointments/'.$appointment->id.'/confirm', [
                'confirmed_time' => '23:00',
            ])
            ->assertSessionHasErrors('confirmed_time');

        $this->assertEquals(AppointmentStatus::PENDING->value, $appointment->refresh()->status);
    }

    public function test_the_student_is_notified_of_the_time_they_are_expected(): void
    {
        Mail::fake();
        Notification::fake();

        [$student, $appointment] = $this->makeStudentWithAppointment();

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/appointments/'.$appointment->id.'/confirm', [
                'confirmed_time' => '10:00',
            ])
            ->assertRedirect();

        // SafeMailer hands the message straight to the transport rather than
        // queueing it, so this is assertSent.
        Mail::assertSent(AppointmentApproved::class, function (AppointmentApproved $mail) use ($student) {
            return $mail->hasTo($student->email)
                && str_contains($mail->render(), '10:00 AM');
        });

        Notification::assertSentTo($student, AppointmentApprovedNotification::class);
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
                'reschedule_reason' => 'Office advisory',
            ])
            ->assertRedirect(route('registrar.appointments.show', $appointment));

        $appointment->refresh();

        $this->assertEquals($oldDate, $appointment->original_date->toDateString());
        $this->assertEquals($newDate, $appointment->date->toDateString());
        $this->assertEquals('Office advisory', $appointment->reschedule_reason);

        Mail::assertSent(AppointmentRescheduled::class, function ($mail) use ($student, $oldDate) {
            return $mail->hasTo($student->email)
                && str_contains($mail->oldSchedule, Carbon::parse($oldDate)->format('F j, Y'));
        });
    }
}
