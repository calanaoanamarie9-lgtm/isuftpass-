<?php

namespace Tests\Feature;

use App\Enums\DocumentRequestStatus;
use App\Models\Document;
use App\Models\User;
use App\Notifications\DocumentRequestStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CashierPaymentModuleTest extends TestCase
{
    use RefreshDatabase;

    private function makeCashier(): User
    {
        return User::factory()->create(['role' => 'cashier']);
    }

    private function makeStudentWithRequest(string $status = DocumentRequestStatus::SUBMITTED->value): array
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
        $this->assertSame(DocumentRequestStatus::SUBMITTED->value, $request->status);

        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2025-00123'])
            ->assertRedirect();

        $fresh = $request->fresh();

        // The registrar's "Paid" step is the `processing` status, so the
        // cashier's payment has to land the request there in one write —
        // no separate approval click to make it read Paid.
        $this->assertNotNull($fresh->paid_at);
        $this->assertEquals(DocumentRequestStatus::PROCESSING->value, $fresh->status);
        $this->assertNotNull($fresh->processing_at);
    }

    public function test_payment_does_not_regress_a_request_the_registrar_already_advanced(): void
    {
        [, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::FOR_SIGNATURE->value);

        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2025-00124'])
            ->assertRedirect();

        $this->assertEquals(DocumentRequestStatus::FOR_SIGNATURE->value, $request->fresh()->status);
    }

    public function test_payment_does_not_resurrect_a_cancelled_request(): void
    {
        [, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::CANCELLED->value);

        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2025-00125'])
            ->assertRedirect();

        $this->assertEquals(DocumentRequestStatus::CANCELLED->value, $request->fresh()->status);
    }

    public function test_registrar_approval_after_payment_moves_to_approved_not_paid(): void
    {
        [, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeCashier())
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2025-00126']);

        // Cashier alone brings it to Paid; the registrar's first click is
        // then the approval the pipeline labels "Approved".
        $this->assertEquals(DocumentRequestStatus::PROCESSING->value, $request->fresh()->status);

        $this->actingAs(User::factory()->create(['role' => 'registrar']))
            ->post('/registrar/document-requests/' . $request->id . '/next')
            ->assertRedirect();

        $this->assertEquals(DocumentRequestStatus::FOR_SIGNATURE->value, $request->fresh()->status);
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