<?php

namespace Tests\Feature;

use App\Enums\DocumentRequestStatus;
use App\Mail\DocumentRequestReadyForPickup;
use App\Models\Appointment;
use App\Models\Document;
use App\Models\User;
use App\Notifications\CashierPaymentDueAlert;
use App\Notifications\DocumentRequestStatusNotification;
use App\Notifications\PaymentPendingNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrarDocumentRequestModuleTest extends TestCase
{
    use RefreshDatabase;

    private function makeRegistrar(): User
    {
        return User::factory()->create(['role' => 'registrar']);
    }

    private function makeStudentWithRequest(string $status = DocumentRequestStatus::SUBMITTED->value): array
    {
        $student = User::factory()->create(['role' => 'student', 'name' => 'Delacruz, Juan Miguel']);
        $document = Document::create(['name' => 'Transcript of Records', 'description' => 'TOR', 'fee' => 100.00]);

        $request = $student->documentRequests()->create([
            'student_name' => $student->name,
            'student_address' => 'Iloilo City',
            'student_contact' => '09170000000',
            'student_course_year' => 'BSIT 3',
            'status' => $status,
            'purpose_type' => 'employment',
            'educational_status' => 'not_graduated',
            'educational_level' => 'college',
            'claim_mode' => 'personal',
            'submitted_at' => now(),
            'paid_at' => now(),
        ]);
        $request->documents()->attach($document->id);

        return [$student, $request];
    }

    public function test_approving_a_request_notifies_the_student_and_every_cashier(): void
    {
        Notification::fake();

        $cashierOne = User::factory()->create(['role' => 'cashier']);
        $cashierTwo = User::factory()->create(['role' => 'cashier']);
        $registrarDesk = User::factory()->create(['role' => 'registrar']);
        [$student, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeRegistrar())
            ->post("/registrar/document-requests/{$request->id}/next")
            ->assertRedirect();

        $this->assertEquals('for_signature', $request->fresh()->status);

        Notification::assertSentToTimes($student, DocumentRequestStatusNotification::class, 1);

        // The student also learns what approval now costs them: the
        // payment step is its own notification, not buried in the status.
        Notification::assertSentTo(
            $student,
            PaymentPendingNotification::class,
            fn ($notification) => str_contains($notification->toArray($student)['message'], '₱100.00')
                && str_contains($notification->toArray($student)['message'], $request->request_number),
        );

        // Approval is the cashiers' cue: the request now waits in their
        // pending queue, so every desk account is told it arrived.
        Notification::assertSentTo($cashierOne, CashierPaymentDueAlert::class);
        Notification::assertSentTo($cashierTwo, CashierPaymentDueAlert::class);

        // The registrar desk acted on it — the alert is the cashier's.
        Notification::assertNotSentTo($registrarDesk, CashierPaymentDueAlert::class);
    }

    public function test_the_cashier_alert_opens_the_pending_queue_filtered_to_the_request(): void
    {
        Notification::fake();

        $cashier = User::factory()->create(['role' => 'cashier']);
        [, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeRegistrar())
            ->post("/registrar/document-requests/{$request->id}/next");

        Notification::assertSentTo(
            $cashier,
            CashierPaymentDueAlert::class,
            fn ($notification) => $notification->toArray($cashier)['url']
                === route('cashier.payments.pending', ['q' => $request->request_number]),
        );
    }

    public function test_the_payment_pending_alert_opens_the_students_own_request_page(): void
    {
        Notification::fake();

        [$student, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeRegistrar())
            ->post("/registrar/document-requests/{$request->id}/next");

        Notification::assertSentTo(
            $student,
            PaymentPendingNotification::class,
            // The student's own request page — behind role:student — is
            // where the claim QR and payment details live.
            fn ($notification) => $notification->toArray($student)['url']
                === route('student.documents.show', $request),
        );
    }

    public function test_setting_the_status_to_approved_directly_also_alerts_cashiers(): void
    {
        Notification::fake();

        $cashier = User::factory()->create(['role' => 'cashier']);
        [, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeRegistrar())
            ->patch("/registrar/document-requests/{$request->id}/status", ['status' => 'for_signature'])
            ->assertRedirect();

        Notification::assertSentTo($cashier, CashierPaymentDueAlert::class);
    }

    public function test_steps_past_approval_do_not_alert_cashiers(): void
    {
        Notification::fake();

        $cashier = User::factory()->create(['role' => 'cashier']);
        [$student, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::READY_FOR_PICKUP->value);

        $this->actingAs($this->makeRegistrar())
            ->post("/registrar/document-requests/{$request->id}/next")
            ->assertRedirect();

        $this->assertEquals('completed', $request->fresh()->status);

        // Releasing and completing are the registrar's own steps — only the
        // approval hands anything to the cashier or costs the student
        // anything.
        Notification::assertSentToTimes($student, DocumentRequestStatusNotification::class, 1);
        Notification::assertNotSentTo($cashier, CashierPaymentDueAlert::class);
        Notification::assertNotSentTo($student, PaymentPendingNotification::class);
    }

    public function test_registrar_can_view_document_requests_pipeline(): void
    {
        [$student, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/document-requests')
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee($request->request_number)
            ->assertSee('Transcript of Records');
    }

    public function test_registrar_archive_tab_shows_only_archived_requests(): void
    {
        [$student, $activeRequest] = $this->makeStudentWithRequest();
        [, $completedRequest] = $this->makeStudentWithRequest(DocumentRequestStatus::COMPLETED->value);

        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/document-requests?tab=archived')
            ->assertOk()
            ->assertSee($completedRequest->request_number)
            ->assertDontSee($activeRequest->request_number);

        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/document-requests?tab=active')
            ->assertOk()
            ->assertSee($activeRequest->request_number)
            ->assertDontSee($completedRequest->request_number);
    }

    public function test_registrar_can_filter_the_archive_tab_to_completed_requests(): void
    {
        [, $activeRequest] = $this->makeStudentWithRequest();
        [, $completedRequest] = $this->makeStudentWithRequest(DocumentRequestStatus::COMPLETED->value);
        [, $cancelledRequest] = $this->makeStudentWithRequest(DocumentRequestStatus::CANCELLED->value);

        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/document-requests?tab=archived&status=completed')
            ->assertOk()
            ->assertSee($completedRequest->request_number)
            ->assertDontSee($cancelledRequest->request_number)
            ->assertDontSee($activeRequest->request_number);
    }

    public function test_registrar_can_view_request_details(): void
    {
        [$student] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/document-requests/' . $student->documentRequests()->first()->id)
            ->assertOk()
            ->assertSee('Document Request Details')
            ->assertSee($student->name);
    }

    public function test_details_page_offers_a_delete_button(): void
    {
        [, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/document-requests/' . $request->id)
            ->assertOk()
            ->assertSee('Delete Request')
            ->assertSee(route('registrar.document-requests.destroy', $request), false);
    }

    public function test_registrar_can_delete_an_active_request(): void
    {
        [, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeRegistrar())
            ->delete('/registrar/document-requests/' . $request->id)
            ->assertRedirect(route('registrar.document-requests.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('document_requests', ['id' => $request->id]);
        $this->assertDatabaseMissing('document_request_document', ['document_request_id' => $request->id]);
    }

    public function test_registrar_can_delete_a_finished_request(): void
    {
        [, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::CANCELLED->value);

        $this->actingAs($this->makeRegistrar())
            ->delete('/registrar/document-requests/' . $request->id)
            ->assertRedirect(route('registrar.document-requests.index'));

        $this->assertDatabaseMissing('document_requests', ['id' => $request->id]);
    }

    public function test_student_cannot_delete_through_the_registrar_route(): void
    {
        [$student, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::CANCELLED->value);

        $this->actingAs($student)
            ->delete('/registrar/document-requests/' . $request->id)
            ->assertForbidden();

        $this->assertDatabaseHas('document_requests', ['id' => $request->id]);
    }

    public function test_registrar_can_set_status_directly_and_student_is_notified(): void
    {
        Mail::fake();

        [$student, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeRegistrar())
            ->patch('/registrar/document-requests/' . $request->id . '/status', [
                'status' => DocumentRequestStatus::READY_FOR_PICKUP->value,
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $request->refresh();

        $this->assertEquals(DocumentRequestStatus::READY_FOR_PICKUP->value, $request->status);
        $this->assertNotNull($request->ready_at);
        $this->assertEquals(1, $student->notifications()->count());

        Mail::assertSent(DocumentRequestReadyForPickup::class, fn ($mail) => $mail->hasTo($student->email));
    }

    public function test_advancing_status_moves_through_pipeline_and_notifies_student(): void
    {
        [$student, $request] = $this->makeStudentWithRequest();
        $request->update(['paid_at' => null]);

        // The registrar approves first, and that is not held up by payment.
        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/next')
            ->assertRedirect();

        $request->refresh();
        $this->assertEquals(DocumentRequestStatus::FOR_SIGNATURE->value, $request->status);
        $this->assertNotNull($request->for_signature_at);

        // At "Approved" there is nothing left to advance — the cashier has
        // to record the payment first.
        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/next')
            ->assertSessionHas('error');

        $this->assertEquals(
            DocumentRequestStatus::FOR_SIGNATURE->value,
            $request->fresh()->status,
        );

        // The cashier's payment is what carries it on to "Paid".
        $this->actingAs(User::factory()->create(['role' => 'cashier']))
            ->post('/cashier/payments/' . $request->id . '/record', ['or_number' => 'OR-PIPELINE-01'])
            ->assertRedirect();

        $this->assertEquals(
            DocumentRequestStatus::PROCESSING->value,
            $request->fresh()->status,
        );

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/next')
            ->assertRedirect();

        $request->refresh();
        $this->assertEquals(DocumentRequestStatus::READY_FOR_PICKUP->value, $request->status);
        $this->assertNotNull($request->ready_at);

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/next')
            ->assertRedirect();

        $request->refresh();
        $this->assertEquals(DocumentRequestStatus::COMPLETED->value, $request->status);
        $this->assertNotNull($request->completed_at);

        // Approve (status + the payment now due), the cashier's payment,
        // release and claim — the blocked attempt notified nobody.
        $this->assertEquals(5, $student->notifications()->count());
        $this->assertEquals(
            4,
            $student->notifications()->where('type', DocumentRequestStatusNotification::class)->count(),
        );
    }

    public function test_completed_request_cannot_be_advanced(): void
    {
        [, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::COMPLETED->value);

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/next')
            ->assertNotFound();
    }

    public function test_registrar_can_approve_a_request_that_has_not_been_paid(): void
    {
        [$student, $request] = $this->makeStudentWithRequest();
        $request->update(['paid_at' => null]);

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/next')
            ->assertRedirect()
            ->assertSessionHas('status');

        $request->refresh();

        $this->assertEquals(DocumentRequestStatus::FOR_SIGNATURE->value, $request->status);
        $this->assertNotNull($request->for_signature_at);

        // The approval status, plus the payment the approval now asks of
        // the student.
        $this->assertEquals(2, $student->notifications()->count());
        $this->assertEquals(
            1,
            $student->notifications()->where('type', PaymentPendingNotification::class)->count(),
        );
    }

    public function test_registrar_cannot_advance_past_approved_until_it_is_paid(): void
    {
        [$student, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::FOR_SIGNATURE->value);
        $request->update(['paid_at' => null]);

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/next')
            ->assertRedirect()
            ->assertSessionHas('error');

        $request->refresh();

        $this->assertEquals(DocumentRequestStatus::FOR_SIGNATURE->value, $request->status);
        $this->assertEquals(0, $student->notifications()->count());
    }

    public function test_registrar_cannot_release_a_request_that_has_not_been_paid(): void
    {
        [, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::PROCESSING->value);
        $request->update(['paid_at' => null]);

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/next')
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertEquals(
            DocumentRequestStatus::PROCESSING->value,
            $request->fresh()->status,
        );
    }

    public function test_registrar_cannot_set_status_directly_on_unpaid_request(): void
    {
        [, $request] = $this->makeStudentWithRequest();
        $request->update(['paid_at' => null]);

        $this->actingAs($this->makeRegistrar())
            ->patch('/registrar/document-requests/' . $request->id . '/status', [
                'status' => DocumentRequestStatus::READY_FOR_PICKUP->value,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertEquals(
            DocumentRequestStatus::SUBMITTED->value,
            $request->fresh()->status,
        );
    }

    public function test_registrar_can_still_reject_an_unpaid_request(): void
    {
        $this->makeStudentWithRequest();
        [, $request] = $this->makeStudentWithRequest();
        $request->update(['paid_at' => null]);

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/cancel')
            ->assertRedirect();

        $this->assertEquals(
            DocumentRequestStatus::CANCELLED->value,
            $request->fresh()->status,
        );
    }

    public function test_show_page_hands_an_approved_request_over_to_the_cashier(): void
    {
        [, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::FOR_SIGNATURE->value);

        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/document-requests/' . $request->id)
            ->assertOk()
            ->assertSee('Waiting for the cashier to record payment')
            ->assertDontSee('Mark as');
    }

    public function test_action_button_shows_the_next_status_it_will_set(): void
    {
        $expectations = [
            DocumentRequestStatus::SUBMITTED->value        => 'Mark as Approved',
            DocumentRequestStatus::PROCESSING->value       => 'Mark as For Release',
            DocumentRequestStatus::READY_FOR_PICKUP->value => 'Mark as Claimed',
        ];

        foreach ($expectations as $status => $buttonLabel) {
            [, $request] = $this->makeStudentWithRequest($status);

            $this->actingAs($this->makeRegistrar())
                ->get('/registrar/document-requests/' . $request->id)
                ->assertOk()
                ->assertSee($buttonLabel);
        }

        // At "Approved" the pipeline is handed to the cashier, so there is
        // no button left for the registrar to press.
        [, $approved] = $this->makeStudentWithRequest(DocumentRequestStatus::FOR_SIGNATURE->value);

        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/document-requests/' . $approved->id)
            ->assertOk()
            ->assertDontSee('Mark as');
    }

    public function test_registrar_can_cancel_request_and_notify_student(): void
    {
        [$student, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/cancel')
            ->assertRedirect();

        $request->refresh();
        $this->assertEquals(DocumentRequestStatus::CANCELLED->value, $request->status);
        $this->assertCount(1, $student->notifications);
    }

    public function test_student_lookup_finds_students(): void
    {
        $student = User::factory()->create(['role' => 'student', 'name' => 'Juan Dela Cruz']);

        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/students?q=Juan')
            ->assertOk()
            ->assertSee('Juan Dela Cruz');
    }

    public function test_student_lookup_show_renders_transactions(): void
    {
        [$student, $request] = $this->makeStudentWithRequest();

        $appointment = $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Certification',
            'date' => now()->addDays(2)->toDateString(),
            'time_slot' => '09:00 AM - 10:00 AM',
            'status' => 'confirmed',
        ]);

        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/students/' . $student->id)
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee('Transcript of Records')
            ->assertSee($appointment->reference_code);
    }

    public function test_qr_verification_matches_payload_token(): void
    {
        $student = User::factory()->create(['role' => 'student', 'name' => 'Maria Santos']);
        $student->studentProfile()->create(['pass_token' => 'token-abc-123']);

        $payload = json_encode([
            'type' => 'isufstpass',
            'id' => $student->id,
            'name' => $student->name,
            'token' => 'token-abc-123',
        ]);

        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/qr-verification?q=' . urlencode($payload))
            ->assertOk()
            ->assertSee('Verified Student')
            ->assertSee('Maria Santos');
    }

    public function test_qr_verification_resolves_document_request_payload(): void
    {
        [, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::READY_FOR_PICKUP->value);

        $payload = json_encode([
            'type' => 'isufstdoc',
            'request' => $request->claim_token,
            'student_id' => $request->user_id,
            'ref' => $request->request_number,
        ]);

        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/qr-verification?q=' . urlencode($payload))
            ->assertOk()
            ->assertSee('Document Request Found')
            ->assertSee($request->request_number)
            ->assertSee('Transcript of Records')
            ->assertSee('Mark as Claimed');
    }

    public function test_qr_verification_claim_marks_request_completed(): void
    {
        [, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::READY_FOR_PICKUP->value);
        $request->update(['claim_token' => 'claim-token-xyz']);

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/next')
            ->assertRedirect();

        $request->refresh();
        $this->assertEquals(DocumentRequestStatus::COMPLETED->value, $request->status);
        $this->assertNotNull($request->completed_at);
    }

    public function test_qr_verification_rejects_unknown_token(): void
    {
        $payload = json_encode([
            'type' => 'isufstpass',
            'id' => 999,
            'name' => 'Ghost',
            'token' => 'unknown-token',
        ]);

        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/qr-verification?q=' . urlencode($payload))
            ->assertOk()
            ->assertSee('No Student Found');
    }

    public function test_student_cannot_access_new_registrar_modules(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get('/registrar/document-requests')
            ->assertForbidden();

        $this->actingAs($student)
            ->get('/registrar/students')
            ->assertForbidden();

        $this->actingAs($student)
            ->get('/registrar/qr-verification')
            ->assertForbidden();
    }
}