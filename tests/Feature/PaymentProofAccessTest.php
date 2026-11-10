<?php

namespace Tests\Feature;

use App\Enums\DocumentRequestStatus;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The proof of payment belongs to whoever paid for it.
 *
 * The cashier's receipt page stays the cashier's (a pinned test elsewhere
 * keeps students out of `/cashier/payments/{id}/receipt`), but the moment a
 * payment is recorded the requester — student or alumni — must be able to
 * open their own copy of the Official Receipt from their own request.
 */
class PaymentProofAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'student',
        ], $attributes));
    }

    private function makePaidRequest(User $owner, bool $paid = true): DocumentRequest
    {
        $document = Document::create(['name' => 'Transcript of Records', 'description' => 'TOR', 'fee' => 100.00]);

        $request = $owner->documentRequests()->create([
            'student_name' => $owner->name,
            'student_address' => 'Iloilo City',
            'student_contact' => '09170000000',
            'student_course_year' => 'BSIT 3',
            'status' => $paid
                ? DocumentRequestStatus::AWAITING_OR_VALIDATION->value
                : DocumentRequestStatus::SUBMITTED->value,
            'purpose_type' => 'employment',
            'educational_status' => 'not_graduated',
            'educational_level' => 'college',
            'claim_mode' => 'personal',
            'submitted_at' => now(),
            'paid_at' => $paid ? now() : null,
            'or_number' => $paid ? 'OR-2026-777' : null,
        ]);
        $request->documents()->attach($document->id);

        return $request;
    }

    private function auditPayment(DocumentRequest $request): void
    {
        AuditLog::create([
            'action' => 'payment.recorded',
            'actor_name' => 'Cashier Staff',
            'role' => 'cashier',
            'description' => "Recorded payment for request {$request->request_number} (OR No. OR-2026-777, ₱100.00). Status is now Paid.",
            'ip_address' => '127.0.0.1',
        ]);
    }

    public function test_the_student_opens_their_official_receipt_as_proof_of_payment(): void
    {
        $student = $this->makeUser();
        $request = $this->makePaidRequest($student);
        $this->auditPayment($request);

        $this->actingAs($student)
            ->get("/student/document-requests/{$request->id}/receipt")
            ->assertOk()
            ->assertSee('Official Receipt')
            ->assertSee('OR-2026-777')
            // The signature line names the desk that recorded the payment,
            // read from the audit trail — not from the requester's session.
            ->assertSee('Cashier Staff')
            ->assertSee('Back to request');
    }

    public function test_an_alumni_requester_opens_their_official_receipt_too(): void
    {
        $alumni = $this->makeUser(['registration_type' => 'alumni']);
        $request = $this->makePaidRequest($alumni);
        $this->auditPayment($request);

        $this->actingAs($alumni)
            ->get("/student/document-requests/{$request->id}/receipt")
            ->assertOk()
            ->assertSee('OR-2026-777')
            ->assertSee('Cashier Staff');
    }

    public function test_another_requester_cannot_open_someone_elses_receipt(): void
    {
        $owner = $this->makeUser();
        $stranger = $this->makeUser();
        $request = $this->makePaidRequest($owner);

        $this->actingAs($stranger)
            ->get("/student/document-requests/{$request->id}/receipt")
            ->assertForbidden();
    }

    public function test_there_is_no_receipt_before_the_payment_is_recorded(): void
    {
        $student = $this->makeUser();
        $request = $this->makePaidRequest($student, paid: false);

        $this->actingAs($student)
            ->get("/student/document-requests/{$request->id}/receipt")
            ->assertNotFound();
    }

    /**
     * The tracking page the receipt links back to must open for alumni as
     * well — they sit behind the same role:student group, but only an
     * actual 200 proves the view needs nothing a graduated account lacks.
     */
    public function test_an_alumni_requester_opens_the_tracking_page(): void
    {
        $alumni = $this->makeUser(['registration_type' => 'alumni']);
        $request = $this->makePaidRequest($alumni);

        $this->actingAs($alumni)
            ->get("/student/document-requests/{$request->id}")
            ->assertOk()
            ->assertSee('Payment Confirmed')
            ->assertSee('View Official Receipt');
    }

    /**
     * The alumni list used to read array keys off Eloquent models, so every
     * badge fell back to "Pending" and no row went anywhere. It now shows
     * the real status and links each row to the tracking page.
     */
    public function test_the_alumni_requests_list_shows_real_status_and_links_each_row(): void
    {
        $alumni = $this->makeUser(['registration_type' => 'alumni']);
        $request = $this->makePaidRequest($alumni);

        $this->actingAs($alumni)
            ->get('/alumni/documents')
            ->assertOk()
            ->assertSee($request->request_number)
            ->assertSee('Paid')
            ->assertSee('OR-2026-777')
            ->assertSee(route('student.documents.show', $request), false);
    }

    /**
     * A paid receipt is still the owner's document: the owner gate holds on
     * the list's destination exactly as it does on the receipt itself.
     */
    public function test_the_tracking_page_of_another_requester_stays_forbidden(): void
    {
        $owner = $this->makeUser();
        $stranger = $this->makeUser();
        $request = $this->makePaidRequest($owner);

        $this->actingAs($stranger)
            ->get("/student/document-requests/{$request->id}")
            ->assertForbidden();
    }
}
