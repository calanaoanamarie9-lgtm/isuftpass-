<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\DocumentRequestStatus;
use App\Mail\AppointmentCancelled;
use App\Mail\DocumentRequestStatusUpdate;
use App\Models\Appointment;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\User;
use App\Notifications\AppointmentCancelledNotification;
use App\Notifications\AppointmentOfficeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationEnhancementTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_payment_sends_payment_confirmation_email(): void
    {
        Mail::fake();

        $cashier = User::factory()->create(['role' => 'cashier']);
        $student = User::factory()->create(['role' => 'student']);
        $document = Document::create(['name' => 'Transcript of Records', 'description' => 'TOR', 'fee' => 150.00]);

        $request = DocumentRequest::create([
            'user_id' => $student->id,
            'student_name' => $student->name,
            'student_address' => 'Iloilo',
            'student_contact' => '09123456789',
            'student_course_year' => 'BSIT 4',
            'status' => DocumentRequestStatus::FOR_SIGNATURE->value,
            'purpose_type' => 'employment',
            'educational_status' => 'graduated',
            'educational_level' => 'college',
            'claim_mode' => 'personal',
            'submitted_at' => now(),
        ]);
        $request->documents()->attach($document->id);

        $this->actingAs($cashier)
            ->post(route('cashier.payments.record', $request), [
                'or_number' => 'OR-998877',
            ])
            ->assertRedirect(route('cashier.payments.receipt', $request));

        $request->refresh();
        $this->assertNotNull($request->paid_at);
        $this->assertEquals('OR-998877', $request->or_number);

        Mail::assertSent(DocumentRequestStatusUpdate::class, function ($mail) use ($student) {
            return $mail->hasTo($student->email)
                && $mail->statusLabel === 'Payment Confirmed';
        });
    }

    public function test_appointment_cancellation_sends_email_and_notification_with_reason(): void
    {
        Mail::fake();
        Notification::fake();

        $registrar = User::factory()->create(['role' => 'registrar']);
        $student = User::factory()->create(['role' => 'student']);

        $appointment = Appointment::create([
            'user_id' => $student->id,
            'office' => 'Registrar',
            'purpose' => 'Transcript',
            'date' => now()->addDays(2)->toDateString(),
            'time_slot' => '09:00 AM - 10:00 AM',
            'status' => AppointmentStatus::PENDING->value,
        ]);

        $this->actingAs($registrar)
            ->post(route('registrar.appointments.cancel', $appointment), [
                'reason' => 'Office will be closed for sanitation.',
            ])
            ->assertRedirect();

        $appointment->refresh();
        $this->assertEquals(AppointmentStatus::CANCELLED->value, $appointment->status);
        $this->assertStringContainsString('sanitation', (string) $appointment->notes);

        Mail::assertSent(AppointmentCancelled::class, function ($mail) use ($student) {
            return $mail->hasTo($student->email)
                && $mail->reason === 'Office will be closed for sanitation.';
        });

        Notification::assertSentTo($student, AppointmentCancelledNotification::class, function ($notification) {
            return $notification->reason === 'Office will be closed for sanitation.';
        });
    }

    public function test_appointment_booking_notifies_office_staff(): void
    {
        Notification::fake();

        $ciciStaff = User::factory()->create([
            'role' => 'department',
            'office' => 'CICI',
            'is_active' => true,
            'approval_status' => 'approved',
        ]);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->post(route('student.appointments.store'), [
                'office' => 'CICI',
                'purpose' => 'Consultation',
                'date' => now()->addDays(2)->toDateString(),
                'time_slot' => '09:00 AM - 10:00 AM',
            ])
            ->assertRedirect();

        Notification::assertSentTo($ciciStaff, AppointmentOfficeNotification::class);
    }
}
