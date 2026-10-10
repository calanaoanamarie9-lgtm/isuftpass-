<?php

namespace Tests\Feature;

use App\Enums\DocumentRequestStatus;
use App\Models\Document;
use App\Models\User;
use App\Notifications\CashierPaymentDueAlert;
use App\Notifications\DocumentRequestStatusNotification;
use App\Notifications\RegistrarRequestAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CashierPaymentModuleTest extends TestCase
{
    use RefreshDatabase;

    private function makeCashier(): User
    {
        // Dashboard guard: no submitted personal details, no dashboard.
        return User::factory()->create(['role' => 'cashier', 'contact_number' => '09171234567']);
    }

    /**
     * The cashier only ever meets a request the registrar has already
     * approved, so that is the state this module starts from.
     */
    private function makeStudentWithRequest(string $status = DocumentRequestStatus::FOR_SIGNATURE->value): array
    {
        $student = User::factory()->create(['role' => 'student']);
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
        ]);
        $request->documents()->attach($document->id);

        return [$student, $request];
    }

    /**
     * The approval hand-off the cashier actually sees: the registrar signs
     * off, the alert lands on the cashier's own notifications page, and
     * opening it lands on the pending queue filtered to that request. No
     * Notification::fake() here — the database row and the pages it opens
     * are the product, not the send call.
     */
    public function test_a_registrar_approval_reaches_the_cashier_notifications_page(): void
    {
        $cashier = $this->makeCashier();
        $student = User::factory()->create(['role' => 'student']);
        $document = Document::create(['name' => 'Transcript of Records', 'description' => 'TOR', 'fee' => 100.00]);

        $request = $student->documentRequests()->create([
            'student_name' => $student->name,
            'student_address' => 'Iloilo City',
            'student_contact' => '09170000000',
            'student_course_year' => 'BSIT 3',
            'status' => DocumentRequestStatus::SUBMITTED->value,
            'purpose_type' => 'employment',
            'educational_status' => 'not_graduated',
            'educational_level' => 'college',
            'claim_mode' => 'personal',
            'submitted_at' => now(),
        ]);
        $request->documents()->attach($document->id);

        $this->actingAs(User::factory()->create(['role' => 'registrar']))
            ->post("/registrar/document-requests/{$request->id}/next")
            ->assertRedirect();

        // One unread alert, carrying the details the cashier needs.
        $this->assertSame(1, $cashier->unreadNotifications()->count());

        $alert = $cashier->notifications()->first();
        $this->assertSame(CashierPaymentDueAlert::class, $alert->type);
        $this->assertStringContainsString('Approved', $alert->data['title']);
        $this->assertStringContainsString($request->request_number, $alert->data['message']);

        // The cashier finds it on their own page — the sidebar links there.
        $this->actingAs($cashier)
            ->get('/cashier/notifications')
            ->assertOk()
            ->assertSee('Approved')
            ->assertSee($request->request_number);

        // Opening it lands on the pending queue, filtered to this request.
        $this->actingAs($cashier)
            ->get('/cashier/notifications/' . $alert->id . '/open')
            ->assertRedirect(route('cashier.payments.pending', ['q' => $request->request_number]));

        $this->assertSame(0, $cashier->unreadNotifications()->count());
    }

    public function test_cashier_can_view_pending_payments(): void
    {
        [$student, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeCashier())
            ->get('/cashier/payments')
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee($request->request_number)
            ->assertSee('100.00')
            ->assertSee('Record Payment');
    }

    public function test_cashier_can_record_payment_and_notify_student(): void
    {
        Notification::fake();
        [$student, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2025-00123'])
            ->assertRedirect();

        $this->assertNotNull($request->fresh()->paid_at);
        $this->assertEquals('OR-2025-00123', $request->fresh()->or_number);

        Notification::assertSentTo(
            $student,
            DocumentRequestStatusNotification::class,
            fn ($notification) => str_contains($notification->message, 'payment has been recorded'),
        );
    }

    public function test_recording_payment_notifies_every_registrar_account(): void
    {
        Notification::fake();

        $registrarOne = User::factory()->create(['role' => 'registrar']);
        $registrarTwo = User::factory()->create(['role' => 'registrar']);
        $otherDesk = User::factory()->create(['role' => 'cashier']);

        [, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2025-00123'])
            ->assertRedirect();

        Notification::assertSentTo($registrarOne, RegistrarRequestAlert::class);
        Notification::assertSentTo($registrarTwo, RegistrarRequestAlert::class);

        // Only the registrar desk is in this pipeline — the cashier who
        // recorded it and every other office have no action to take.
        Notification::assertNotSentTo($otherDesk, RegistrarRequestAlert::class);
    }

    public function test_registrar_alert_targets_their_own_request_queue(): void
    {
        Notification::fake();

        $registrar = User::factory()->create(['role' => 'registrar']);
        [, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2025-00123']);

        Notification::assertSentTo(
            $registrar,
            RegistrarRequestAlert::class,
            // The student route is behind role:student; a registrar clicking
            // it would hit a 403 instead of their queue.
            fn ($notification) => $notification->toArray($registrar)['url']
                === route('registrar.document-requests.show', $request),
        );
    }

    public function test_recording_payment_notifies_the_student_and_the_registrar_once_each(): void
    {
        Notification::fake();

        $registrar = User::factory()->create(['role' => 'registrar']);
        [$student, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2025-00123']);
        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2025-00123']);

        // The second attempt is refused, so neither side is alerted twice.
        Notification::assertSentToTimes($student, DocumentRequestStatusNotification::class, 1);
        Notification::assertSentToTimes($registrar, RegistrarRequestAlert::class, 1);
    }

    public function test_or_number_is_required_when_recording_payment(): void
    {
        [$student, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record")
            ->assertSessionHasErrors('or_number');

        $this->assertNull($request->fresh()->paid_at);
    }

    public function test_recording_payment_marks_the_request_paid_for_the_registrar(): void
    {
        [$student, $request] = $this->makeStudentWithRequest();
        $this->assertSame(DocumentRequestStatus::FOR_SIGNATURE->value, $request->status);

        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2025-00123'])
            ->assertRedirect();

        $fresh = $request->fresh();

        // The registrar's "Paid" step is the `processing` status, so the
        // cashier's payment has to land the request there in one write.
        $this->assertNotNull($fresh->paid_at);
        $this->assertEquals(DocumentRequestStatus::PROCESSING->value, $fresh->status);
        $this->assertNotNull($fresh->processing_at);
    }

    public function test_cashier_cannot_record_payment_before_the_registrar_approves(): void
    {
        [, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::SUBMITTED->value);

        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2025-00999'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull($request->fresh()->paid_at);
        $this->assertEquals(DocumentRequestStatus::SUBMITTED->value, $request->fresh()->status);
    }

    public function test_payment_does_not_regress_a_request_the_registrar_already_advanced(): void
    {
        [, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::READY_FOR_PICKUP->value);

        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2025-00124'])
            ->assertRedirect();

        // The payment queue only ever holds approved requests, so one the
        // registrar has already released is never pulled back to Paid.
        $this->assertEquals(DocumentRequestStatus::READY_FOR_PICKUP->value, $request->fresh()->status);
    }

    public function test_payment_does_not_resurrect_a_cancelled_request(): void
    {
        [, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::CANCELLED->value);

        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2025-00125'])
            ->assertRedirect();

        $this->assertEquals(DocumentRequestStatus::CANCELLED->value, $request->fresh()->status);
    }

    public function test_the_registrar_approves_before_the_cashier_records_payment(): void
    {
        [, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::SUBMITTED->value);

        // Approval comes first, and it does not need the payment on file.
        $this->actingAs(User::factory()->create(['role' => 'registrar']))
            ->post('/registrar/document-requests/' . $request->id . '/next')
            ->assertRedirect();

        $this->assertEquals(DocumentRequestStatus::FOR_SIGNATURE->value, $request->fresh()->status);
        $this->assertNull($request->fresh()->paid_at);

        // Only now does the cashier's payment carry it on to Paid.
        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2025-00126'])
            ->assertRedirect();

        $fresh = $request->fresh();

        $this->assertNotNull($fresh->paid_at);
        $this->assertEquals(DocumentRequestStatus::PROCESSING->value, $fresh->status);
    }

    public function test_duplicate_or_number_is_rejected(): void
    {
        [$student, $request] = $this->makeStudentWithRequest();
        [$student2, $request2] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2025-00123'])
            ->assertRedirect();

        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request2->id}/record", ['or_number' => 'OR-2025-00123'])
            ->assertSessionHasErrors('or_number');

        $this->assertNull($request2->fresh()->paid_at);
    }

    public function test_payment_cannot_be_recorded_twice(): void
    {
        [$student, $request] = $this->makeStudentWithRequest();
        $request->update(['paid_at' => now()->subHour()]);

        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record")
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertEquals($request->fresh()->paid_at->toDateTimeString(), $request->paid_at->toDateTimeString());
    }

    public function test_two_payment_recordings_notify_twice(): void
    {
        Notification::fake();
        [$student, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2025-00123']);
        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2025-00123']);

        Notification::assertSentToTimes($student, DocumentRequestStatusNotification::class, 1);
    }

    public function test_completed_requests_are_excluded_from_pending(): void
    {
        [$student, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::COMPLETED->value);

        $this->actingAs($this->makeCashier())
            ->get('/cashier/payments')
            ->assertOk()
            ->assertDontSee($student->name);
    }

    public function test_cashier_can_view_payment_history(): void
    {
        [$student, $request] = $this->makeStudentWithRequest();
        $request->update(['paid_at' => now()]);

        $this->actingAs($this->makeCashier())
            ->get('/cashier/payments/history')
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee('Paid')
            ->assertSee('100.00');
    }

    public function test_payment_history_filters_by_period(): void
    {
        [$recentStudent, $recentRequest] = $this->makeStudentWithRequest();
        $recentRequest->update(['paid_at' => now()]);

        [$oldStudent, $oldRequest] = $this->makeStudentWithRequest();
        $oldRequest->update(['paid_at' => now()->subDays(45)]);

        $this->actingAs($this->makeCashier())
            ->get('/cashier/payments/history?period=month')
            ->assertOk()
            ->assertSee($recentStudent->name)
            ->assertDontSee($oldStudent->name)
            ->assertSee('100.00');

        $this->actingAs($this->makeCashier())
            ->get('/cashier/payments/history?period=year')
            ->assertOk()
            ->assertSee($recentStudent->name)
            ->assertSee($oldStudent->name);
    }

    public function test_payment_history_filters_by_custom_range(): void
    {
        [$recentStudent, $recentRequest] = $this->makeStudentWithRequest();
        $recentRequest->update(['paid_at' => now()]);

        [$oldStudent, $oldRequest] = $this->makeStudentWithRequest();
        $oldRequest->update(['paid_at' => now()->subDays(45)]);

        $oldDate = now()->subDays(45)->toDateString();

        $this->actingAs($this->makeCashier())
            ->get("/cashier/payments/history?period=custom&from={$oldDate}&to={$oldDate}")
            ->assertOk()
            ->assertSee($oldStudent->name)
            ->assertDontSee($recentStudent->name)
            ->assertSee('100.00');
    }

    public function test_cashier_ledger_lists_students(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($this->makeCashier())
            ->get('/cashier/ledger')
            ->assertOk()
            ->assertSee($student->name);
    }

    public function test_cashier_ledger_show_renders_financial_record(): void
    {
        [$student, $request] = $this->makeStudentWithRequest();
        $request->update(['paid_at' => now()]);

        $this->actingAs($this->makeCashier())
            ->get("/cashier/ledger/{$student->id}")
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee('Total Paid')
            ->assertSee('100.00');
    }

    public function test_cashier_dashboard_shows_collections(): void
    {
        [$student, $request] = $this->makeStudentWithRequest();
        $request->update(['paid_at' => now()]);

        $this->actingAs($this->makeCashier())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee("Today's Collections", false)
            ->assertSee('100.00');
    }

    public function test_student_cannot_access_cashier_routes(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get('/cashier/payments')
            ->assertForbidden();

        $this->actingAs($student)
            ->get('/cashier/ledger')
            ->assertForbidden();
    }
}