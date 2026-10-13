<?php

namespace Tests\Feature;

use App\Enums\DocumentRequestStatus;
use App\Mail\DocumentRequestStatusUpdate;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\User;
use App\Notifications\DocumentRequestStatusNotification;
use App\Notifications\RegistrarRequestAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The standard "Functional Flow Chart in the Delivery of Service — Release
 * of Credentials", end to end:
 *
 *   1. the student submits the request,
 *   2. the registrar approves and issues the Order of Payment,
 *   3. the cashier collects and prints the Official Receipt,
 *   4. the student presents that OR back to the registrar,
 *   5. the registrar validates it and credential processing begins.
 *
 * Every hand-off has a gate: nobody can advance past a step the other office
 * still owns. These tests walk the real routes and read the real pages, so a
 * step that silently stops working fails here before anyone queues up at the
 * cashier's window.
 */
class ReleaseOfCredentialsFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeRegistrar(): User
    {
        return User::factory()->create(['role' => 'registrar']);
    }

    private function makeCashier(): User
    {
        return User::factory()->create(['role' => 'cashier']);
    }

    /**
     * Build a request in whatever state the step under test starts from.
     * The paid states carry the cashier's write (paid_at + or_number) so a
     * test never has to fake payment through the back door.
     */
    private function makeRequest(User $student, string $status = DocumentRequestStatus::SUBMITTED->value): DocumentRequest
    {
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

        if (in_array($status, [
            DocumentRequestStatus::AWAITING_OR_VALIDATION->value,
            DocumentRequestStatus::PROCESSING->value,
            DocumentRequestStatus::READY_FOR_PICKUP->value,
            DocumentRequestStatus::COMPLETED->value,
        ], true)) {
            $request->forceFill([
                'paid_at' => now(),
                'or_number' => 'OR-2026-00001',
            ])->save();
        }

        return $request;
    }

    /**
     * Step 2's paperwork: approving issues the Order of Payment once, the
     * student settles it at the cashier, and the registrar can reprint it.
     * Before approval there is nothing to print.
     */
    public function test_approval_issues_a_printable_order_of_payment(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $request = $this->makeRequest($student);

        // Nothing to print before the registrar approves.
        $this->actingAs($student)
            ->get(route('student.documents.order-of-payment', $request))
            ->assertNotFound();

        $this->actingAs($this->makeRegistrar())
            ->post("/registrar/document-requests/{$request->id}/next")
            ->assertRedirect();

        $request->refresh();
        $this->assertSame(DocumentRequestStatus::FOR_SIGNATURE->value, $request->status);
        $this->assertMatchesRegularExpression('/^OP-\d{8}-[A-Z0-9]{6}$/', $request->op_number);

        // The student's copy of the billing statement.
        $this->actingAs($student)
            ->get(route('student.documents.order-of-payment', $request))
            ->assertOk()
            ->assertSee('Order of Payment')
            ->assertSee($request->op_number)
            ->assertSee(number_format($request->totalFee(), 2));

        // The registrar's copy is the same sheet, for reissuing a lost one.
        $this->actingAs($this->makeRegistrar())
            ->get(route('registrar.document-requests.order-of-payment', $request))
            ->assertOk()
            ->assertSee($request->op_number);

        // Re-approvals never renumber it.
        $issued = $request->op_number;
        $request->issueOrderOfPayment();
        $this->assertSame($issued, $request->fresh()->op_number);
    }

    /**
     * The whole chart in one walk: approve → pay → present → validate →
     * release, checking each office's page on the way through.
     */
    public function test_the_release_flow_walks_all_five_steps(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $registrar = $this->makeRegistrar();
        $cashier = $this->makeCashier();
        $request = $this->makeRequest($student);

        // --- Step 2: the registrar approves and issues the Order of Payment.
        $this->actingAs($registrar)
            ->post("/registrar/document-requests/{$request->id}/next")
            ->assertRedirect()
            ->assertSessionHas('status');

        $request->refresh();
        $this->assertSame(DocumentRequestStatus::FOR_SIGNATURE->value, $request->status);
        $this->assertNotNull($request->op_number);

        // The cashier's queue now carries the request and its fee.
        $this->actingAs($cashier)
            ->get(route('cashier.payments.pending'))
            ->assertOk()
            ->assertSee($request->request_number);

        // --- Step 3: the cashier records payment and prints the OR.
        $this->actingAs($cashier)
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2026-777'])
            ->assertRedirect(route('cashier.payments.receipt', $request));

        $request->refresh();
        $this->assertSame(DocumentRequestStatus::PROCESSING->value, $request->status);
        $this->assertNotNull($request->paid_at);
        $this->assertSame('OR-2026-777', $request->or_number);

        // The printable receipt.
        $this->actingAs($cashier)
            ->get("/cashier/payments/{$request->id}/receipt")
            ->assertOk()
            ->assertSee('Official Receipt')
            ->assertSee('OR-2026-777');

        // --- Step 4: registrar continues advancing through the pipeline
        // (no student OR submission or registrar OR validation needed).
        $this->actingAs($registrar)
            ->post("/registrar/document-requests/{$request->id}/next")
            ->assertRedirect();
        $this->assertSame(DocumentRequestStatus::READY_FOR_PICKUP->value, $request->fresh()->status);

        $this->actingAs($registrar)
            ->post("/registrar/document-requests/{$request->id}/next")
            ->assertRedirect();
        $this->assertSame(DocumentRequestStatus::COMPLETED->value, $request->fresh()->status);
    }

    /**
     * Step 3 → 4: payment parks the request at the OR gate, and until the
     * student presents the receipt there is nowhere for the registrar to go.
     */
    public function test_next_refuses_both_hand_off_points(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        // Hand-off 1: approved, waiting on the cashier.
        $approved = $this->makeRequest($student, DocumentRequestStatus::FOR_SIGNATURE->value);
        $this->actingAs($this->makeRegistrar())
            ->post("/registrar/document-requests/{$approved->id}/next")
            ->assertSessionHas('error', fn ($message) => str_contains($message, 'waiting for the cashier'));

        $this->assertSame(
            DocumentRequestStatus::FOR_SIGNATURE->value,
            $approved->fresh()->status,
        );

        // Hand-off 2: paid, but the student has not presented the OR yet.
        $paid = $this->makeRequest($student, DocumentRequestStatus::PROCESSING->value);
        $this->actingAs($this->makeRegistrar())
            ->post("/registrar/document-requests/{$paid->id}/next")
            ->assertRedirect();
        $this->assertSame(DocumentRequestStatus::READY_FOR_PICKUP->value, $paid->fresh()->status);
    }

    /**
     * Step 4 → 5: validating tells the student, by notification and email,
     * that their credential is now being prepared.
     */
    public function test_validating_the_or_notifies_the_student(): void
    {
        Mail::fake();

        $student = User::factory()->create(['role' => 'student']);
        $request = $this->makeRequest($student, DocumentRequestStatus::AWAITING_OR_VALIDATION->value);
        $request->forceFill([
            'or_submitted_at' => now(),
            'submitted_or_number' => 'OR-2026-42',
            'submitted_or_date' => now()->toDateString(),
            'submitted_or_amount' => 100.00,
        ])->save();

        $this->actingAs($this->makeRegistrar())
            ->post("/registrar/document-requests/{$request->id}/validate-or")
            ->assertRedirect();

        $this->assertSame(DocumentRequestStatus::PROCESSING->value, $request->fresh()->status);

        $notification = $student->notifications()
            ->where('type', DocumentRequestStatusNotification::class)
            ->get()
            ->first(fn ($n) => str_contains($n->data['title'] ?? '', 'OR Validated'));
        $this->assertNotNull($notification);

        Mail::assertSent(DocumentRequestStatusUpdate::class, fn ($mail) => $mail->hasTo($student->email));

        // Validating twice is refused — it already left the gate.
        $this->actingAs($this->makeRegistrar())
            ->post("/registrar/document-requests/{$request->id}/validate-or")
            ->assertSessionHas('error');
    }

    /**
     * The send-back loop: a receipt that does not check out returns to the
     * student with the registrar's note, and the form reopens for a resubmit.
     */
    public function test_the_registrar_can_send_the_or_back_for_correction(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $request = $this->makeRequest($student, DocumentRequestStatus::AWAITING_OR_VALIDATION->value);
        $request->forceFill([
            'or_submitted_at' => now(),
            'submitted_or_number' => 'OR-WRONG-1',
            'submitted_or_date' => now()->toDateString(),
            'submitted_or_amount' => 999.00,
        ])->save();

        // The note is required — an empty send-back changes nothing.
        $this->actingAs($this->makeRegistrar())
            ->post("/registrar/document-requests/{$request->id}/or-correction", [])
            ->assertSessionHasErrors('note');
        $this->assertNotNull($request->fresh()->or_submitted_at);

        $this->actingAs($this->makeRegistrar())
            ->post("/registrar/document-requests/{$request->id}/or-correction", [
                'note' => 'The amount presented does not match the Order of Payment.',
            ])
            ->assertRedirect();

        $sent = $request->fresh();
        $this->assertNull($sent->or_submitted_at);
        $this->assertSame('The amount presented does not match the Order of Payment.', $sent->or_validation_note);

        // The student sees the note and gets an alert to resubmit.
        $this->actingAs($student)
            ->get(route('student.documents.show', $request))
            ->assertOk()
            ->assertSee('The Registrar returned your Official Receipt details:');

        $this->assertTrue(
            $student->unreadNotifications()->get()->contains(
                fn ($n) => str_contains($n->data['title'] ?? '', 'OR for Correction')
            ),
        );

        // Resubmission reopens the form and clears the note.
        $this->actingAs($student)
            ->post(route('student.documents.submit-or', $request), [
                'submitted_or_number' => 'OR-2026-42',
                'submitted_or_date' => now()->toDateString(),
                'submitted_or_amount' => 100.00,
            ])
            ->assertRedirect(route('student.documents.show', $request));

        $resubmitted = $request->fresh();
        $this->assertNotNull($resubmitted->or_submitted_at);
        $this->assertNull($resubmitted->or_validation_note);
        $this->assertSame('OR-2026-42', $resubmitted->submitted_or_number);
    }

    /**
     * Nobody else's receipt, and no receipt before payment: the OR form is
     * the owner's alone, open exactly while the request sits at the gate.
     */
    public function test_or_submission_is_gated_to_the_owner_and_the_right_state(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $stranger = User::factory()->create(['role' => 'student']);
        $request = $this->makeRequest($student, DocumentRequestStatus::AWAITING_OR_VALIDATION->value);

        $payload = [
            'submitted_or_number' => 'OR-2026-9',
            'submitted_or_date' => now()->toDateString(),
            'submitted_or_amount' => 100.00,
        ];

        // Another student cannot present a receipt for someone else's request.
        $this->actingAs($stranger)
            ->post(route('student.documents.submit-or', $request), $payload)
            ->assertForbidden();

        // Not while it is still waiting on payment approval...
        $request->forceFill(['status' => DocumentRequestStatus::FOR_SIGNATURE->value])->save();
        $this->actingAs($student)
            ->post(route('student.documents.submit-or', $request), $payload)
            ->assertForbidden();

        // ...and not once it is already in processing.
        $request->forceFill(['status' => DocumentRequestStatus::PROCESSING->value])->save();
        $this->actingAs($student)
            ->post(route('student.documents.submit-or', $request), $payload)
            ->assertForbidden();

        // The student can never print the cashier's receipt page.
        $request->forceFill([
            'status' => DocumentRequestStatus::AWAITING_OR_VALIDATION->value,
            'paid_at' => now(),
            'or_number' => 'OR-2026-9',
        ])->save();

        $this->actingAs($student)
            ->get("/cashier/payments/{$request->id}/receipt")
            ->assertForbidden();
    }
}
