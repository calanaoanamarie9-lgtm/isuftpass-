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

    /**
     * Every bookable desk — the four colleges, SSC, Accounting, Library and
     * the Registrar — must read the same dynamic sentence on its own queue:
     * the requester's exact name, their classification (Student, Alumni,
     * Parent, Guest), the office they booked and the date. Never the
     * generic "A student".
     */
    public function test_office_notification_names_the_requester_and_role_at_every_desk(): void
    {
        Mail::fake();

        $requester = User::factory()->create([
            'role' => 'student',
            'registration_type' => 'alumni',
            'name' => 'Juan Dela Cruz',
        ]);

        $when = now()->addDays(3);
        $date = $when->toDateString();
        $dateLabel = $when->format('F j, Y');

        foreach (['SSC', 'Registrar', 'Accounting', 'Library', 'CICI', 'CBMSD', 'COAG', 'COED'] as $office) {
            $staff = User::factory()->create([
                'role' => 'department',
                'office' => $office,
                'is_active' => true,
                'approval_status' => User::APPROVAL_APPROVED,
            ]);

            $this->actingAs($requester)
                ->post(route('student.appointments.store'), [
                    'office' => $office,
                    'purpose' => 'Consultation',
                    'date' => $date,
                ])
                ->assertRedirect(route('student.appointments.index'));

            $notification = $staff->notifications()->first();

            $this->assertNotNull($notification, "The {$office} desk must receive the booking alert.");

            $this->assertSame(
                "Juan Dela Cruz (Alumni) has requested an appointment with the {$office} office on {$dateLabel}",
                $notification->data['message'],
                "The {$office} desk's alert must name the requester and their role.",
            );
        }
    }

    /**
     * The role inside the parentheses is the requester's own classification,
     * so each public account type reads as the label the desk expects.
     */
    public function test_office_notification_carries_the_requesters_own_role_label(): void
    {
        Mail::fake();

        $staff = User::factory()->create([
            'role' => 'department',
            'office' => 'Library',
            'is_active' => true,
            'approval_status' => User::APPROVAL_APPROVED,
        ]);

        $requesters = [
            ['Student', []],
            ['Alumni', ['registration_type' => 'alumni']],
            ['Guest', ['registration_type' => 'guest']],
            ['Parent', ['registration_type' => 'parent']],
        ];

        foreach ($requesters as [$label, $attrs]) {
            $user = User::factory()->create([
                'role' => 'student',
                'name' => "Requester {$label}",
                ...$attrs,
            ]);

            $payload = [
                'office' => 'Library',
                'purpose' => 'Consultation',
                'date' => now()->addDays(4)->toDateString(),
            ];

            // A parent must state the child's Student ID when booking.
            if ($label === 'Parent') {
                $payload['student_id'] = 'ISUFST-2024-0009';
            }

            $this->actingAs($user)
                ->post(route('student.appointments.store'), $payload)
                ->assertRedirect(route('student.appointments.index'));
        }

        $messages = $staff->notifications()->get()->pluck('data.message');

        $this->assertCount(4, $messages);

        foreach ($requesters as [$label]) {
            $this->assertTrue(
                $messages->contains(fn (string $message) => str_contains($message, "Requester {$label} ({$label}) has requested an appointment")),
                "No alert carries the {$label} label for requester Requester {$label}.",
            );
        }
    }

    /**
     * The document-request creation alert landing on the registrar's queue
     * names the requester and their classification too — the same rule as
     * the appointment alert, on the other pipeline.
     */
    public function test_document_request_alert_names_the_requester_and_role(): void
    {
        Mail::fake();

        $registrar = User::factory()->create([
            'role' => 'registrar',
            'is_active' => true,
            'approval_status' => User::APPROVAL_APPROVED,
        ]);

        $alumni = User::factory()->create([
            'role' => 'student',
            'registration_type' => 'alumni',
            'name' => 'Juan Dela Cruz',
        ]);

        $document = Document::create([
            'name' => 'Transcript of Records',
            'description' => 'TOR',
            'fee' => 150.00,
            'audience' => Document::AUDIENCE_BOTH,
        ]);

        $this->actingAs($alumni)
            ->post('/student/document-requests', [
                'document_ids' => [$document->id],
                'purpose_type' => 'employment',
                'educational_status' => 'graduated',
                'educational_level' => 'college',
                'claim_mode' => 'personal',
            ])
            ->assertRedirect(route('student.documents.index'));

        $notification = $registrar->notifications()->first();

        $this->assertNotNull($notification, 'The registrar desk must receive the document-request alert.');
        $this->assertStringContainsString(
            'Juan Dela Cruz (Alumni) submitted a request for',
            $notification->data['message'],
        );
        $this->assertStringContainsString(
            'It is waiting in your queue.',
            $notification->data['message'],
        );
    }

    /**
     * The shared notifications page every role's panel is built from must
     * output the whole dynamic message — name, role, office and date — with
     * no truncation applied to the line.
     */
    public function test_notifications_page_renders_the_full_dynamic_message(): void
    {
        $registrar = User::factory()->create(['role' => 'registrar']);
        $requester = User::factory()->create([
            'role' => 'student',
            'registration_type' => 'alumni',
            'name' => 'Juan Dela Cruz',
        ]);

        $appointment = Appointment::create([
            'user_id' => $requester->id,
            'office' => 'Library',
            'purpose' => 'Consultation',
            'date' => now()->addDays(5)->toDateString(),
            'time_slot' => null,
            'status' => AppointmentStatus::PENDING->value,
        ]);

        $registrar->notify(new AppointmentOfficeNotification($appointment));

        $expected = sprintf(
            'Juan Dela Cruz (Alumni) has requested an appointment with the Library office on %s',
            $appointment->date->format('F j, Y'),
        );

        $response = $this->actingAs($registrar)
            ->get('/registrar/notifications')
            ->assertOk()
            ->assertSee($expected);

        // The paragraph that carries the dynamic message must wrap it whole —
        // no truncate/line-clamp class on that specific line may clip the
        // requester's name or role out of view. (Other elements, like the
        // sidebar, use truncate legitimately; only this line is checked.)
        $html = $response->getContent();
        $offset = strpos($html, 'Juan Dela Cruz (Alumni) has requested');

        $this->assertNotFalse($offset, 'The full dynamic message must be present in the page.');

        $tagStart = strrpos(substr($html, 0, $offset), '<p');
        $this->assertNotFalse($tagStart, 'The message must be rendered inside a paragraph.');
        $tagEnd = strpos($html, '>', $tagStart);
        $messageTag = substr($html, $tagStart, $tagEnd - $tagStart + 1);

        $this->assertStringContainsString('text-gray-600', $messageTag);
        $this->assertStringNotContainsString('truncate', $messageTag);
        $this->assertStringNotContainsString('line-clamp', $messageTag);
    }
}
