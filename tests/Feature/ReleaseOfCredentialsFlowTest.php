<?php

namespace Tests\Feature;

use App\Enums\DocumentRequestStatus;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The standard "Functional Flow Chart in the Delivery of Service — Release
 * of Credentials", end to end:
 *
 *   1. the student submits the request,
 *   2. the registrar approves and issues the Order of Payment,
 *   3. the cashier collects and prints the Official Receipt (Paid),
 *   4. the registrar explicitly clicks Start Processing,
 *   5. the credential is released for claim.
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
     * The whole chart in one walk: approve → pay → start processing →
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

        // --- Step 3: the cashier records payment and prints the OR. This
        // parks the request at PAID — processing has not started yet.
        $this->actingAs($cashier)
            ->post("/cashier/payments/{$request->id}/record", ['or_number' => 'OR-2026-777'])
            ->assertRedirect(route('cashier.payments.receipt', $request));

        $request->refresh();
        $this->assertSame(DocumentRequestStatus::AWAITING_OR_VALIDATION->value, $request->status);
        $this->assertNotNull($request->paid_at);
        $this->assertSame('OR-2026-777', $request->or_number);

        // The printable receipt.
        $this->actingAs($cashier)
            ->get("/cashier/payments/{$request->id}/receipt")
            ->assertOk()
            ->assertSee('Official Receipt')
            ->assertSee('OR-2026-777');

        // --- Step 4: the registrar explicitly clicks Start Processing.
        $this->actingAs($registrar)
            ->post("/registrar/document-requests/{$request->id}/next")
            ->assertRedirect();
        $this->assertSame(DocumentRequestStatus::PROCESSING->value, $request->fresh()->status);

        // --- Step 5: release, then claim.
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
     * The hand-off that still refuses the registrar: at "Approved" the
     * cashier owns the next move, so advancing fails until payment lands.
     * Once paid, Start Processing becomes the registrar's explicit click.
     */
    public function test_next_refuses_the_cashier_hand_off(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        // Hand-off: approved, waiting on the cashier.
        $approved = $this->makeRequest($student, DocumentRequestStatus::FOR_SIGNATURE->value);
        $this->actingAs($this->makeRegistrar())
            ->post("/registrar/document-requests/{$approved->id}/next")
            ->assertSessionHas('error', fn ($message) => str_contains($message, 'waiting for the cashier'));

        $this->assertSame(
            DocumentRequestStatus::FOR_SIGNATURE->value,
            $approved->fresh()->status,
        );

        // Once the cashier has recorded the payment, Start Processing works...
        $paid = $this->makeRequest($student, DocumentRequestStatus::AWAITING_OR_VALIDATION->value);
        $this->actingAs($this->makeRegistrar())
            ->post("/registrar/document-requests/{$paid->id}/next")
            ->assertRedirect();
        $this->assertSame(DocumentRequestStatus::PROCESSING->value, $paid->fresh()->status);

        // ...and the payment gate still holds the release step.
        $this->actingAs($this->makeRegistrar())
            ->post("/registrar/document-requests/{$paid->id}/next")
            ->assertRedirect();
        $this->assertSame(DocumentRequestStatus::READY_FOR_PICKUP->value, $paid->fresh()->status);
    }

    /**
     * The student can never print the cashier's receipt page — that PDF is
     * the cashier's to hand over at the window.
     */
    public function test_the_student_cannot_print_the_cashier_receipt_page(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $request = $this->makeRequest($student, DocumentRequestStatus::AWAITING_OR_VALIDATION->value);

        $this->actingAs($student)
            ->get("/cashier/payments/{$request->id}/receipt")
            ->assertForbidden();
    }
}
