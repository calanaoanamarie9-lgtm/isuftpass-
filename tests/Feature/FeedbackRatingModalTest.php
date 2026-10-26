<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * The post-submission rating modal: the request forms post in the
 * background, the modal collects a 1–5 star rating, and the JSON endpoint
 * records it against the transaction before the page moves on.
 */
class FeedbackRatingModalTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudent(string $email = 'student@example.com'): User
    {
        $user = User::factory()->create(['role' => 'student', 'email' => $email]);

        $user->studentProfile()->create([
            'course' => 'BSIT',
            'year_level' => '3rd Year',
            'contact_number' => '09171234567',
            'address' => 'Barangay Uno, Iloilo City',
        ]);

        return $user;
    }

    private function makeDocument(): Document
    {
        return Document::create([
            'name' => 'Transcript of Record (TOR)',
            'description' => 'TOR',
            'fee' => 100.00,
        ]);
    }

    private function documentPayload(Document $document): array
    {
        return [
            'document_ids' => [$document->id],
            'purpose_type' => 'employment',
            'educational_status' => 'not_graduated',
            'educational_level' => 'college',
            'claim_mode' => 'personal',
        ];
    }

    private function submitDocumentRequest(User $user): DocumentRequest
    {
        Mail::fake();
        Notification::fake();

        $document = $this->makeDocument();

        $this->actingAs($user)
            ->postJson('/student/document-requests', $this->documentPayload($document))
            ->assertOk();

        return DocumentRequest::latest('id')->firstOrFail();
    }

    public function test_the_document_request_form_opens_the_rating_modal_after_submitting(): void
    {
        $this->actingAs($this->makeStudent())
            ->get('/student/document-requests/create')
            ->assertOk()
            ->assertSee('How was your experience?')
            ->assertSee('data-feedback-form', false)
            ->assertSee('Skip for Now')
            ->assertSee('Submit Feedback');
    }

    public function test_the_appointment_form_opens_the_rating_modal_after_submitting(): void
    {
        $this->actingAs($this->makeStudent())
            ->get('/student/appointments/create')
            ->assertOk()
            ->assertSee('How was your experience?')
            ->assertSee('data-feedback-form', false);
    }

    public function test_a_document_request_submitted_in_the_background_returns_feedback_context(): void
    {
        Mail::fake();
        Notification::fake();

        $user = $this->makeStudent();
        $document = $this->makeDocument();

        $response = $this->actingAs($user)
            ->postJson('/student/document-requests', $this->documentPayload($document));

        $request = DocumentRequest::latest('id')->firstOrFail();

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('feedback.transactionType', Feedback::TYPE_DOCUMENT_REQUEST)
            ->assertJsonPath('feedback.transactionId', $request->id)
            ->assertJsonPath('feedback.redirect', route('student.documents.index'));

        // The confirmation still greets the student on the page they land on.
        $response->assertSessionHas('swal', 'Request Successfully Submitted')
            ->assertSessionHas('status')
            ->assertSessionHas('instructions', $request->id);
    }

    public function test_an_appointment_submitted_in_the_background_returns_feedback_context(): void
    {
        Mail::fake();
        Notification::fake();

        $user = $this->makeStudent();

        $response = $this->actingAs($user)
            ->postJson(route('student.appointments.store'), [
                'office' => 'Registrar',
                'purpose' => 'Transcript of Records',
                'date' => now()->addDays(3)->toDateString(),
            ]);

        $appointment = Appointment::latest('id')->firstOrFail();

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('feedback.transactionType', Feedback::TYPE_APPOINTMENT)
            ->assertJsonPath('feedback.transactionId', $appointment->id)
            ->assertJsonPath('feedback.redirect', route('student.appointments.index'));

        $response->assertSessionHas('status');
    }

    public function test_a_validation_failure_is_still_a_json_error_the_form_can_show(): void
    {
        Mail::fake();
        Notification::fake();

        $user = $this->makeStudent();

        $this->actingAs($user)
            ->postJson('/student/document-requests', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['document_ids', 'purpose_type']);
    }

    public function test_the_feedback_endpoint_records_one_rating_per_transaction(): void
    {
        $user = $this->makeStudent();
        $request = $this->submitDocumentRequest($user);

        $payload = [
            'transaction_type' => Feedback::TYPE_DOCUMENT_REQUEST,
            'transaction_id' => $request->id,
            'rating' => 5,
            'comments' => 'Fast and clear instructions.',
        ];

        $this->actingAs($user)
            ->postJson('/api/feedback/store', $payload)
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('duplicate', false)
            ->assertJsonPath('feedback.rating', 5);

        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $user->id,
            'feedbackable_type' => DocumentRequest::class,
            'feedbackable_id' => $request->id,
            'transaction_type' => Feedback::TYPE_DOCUMENT_REQUEST,
            'rating' => 5,
            'comment' => 'Fast and clear instructions.',
        ]);

        // A retry (dropped connection, double tap) must not add a second row.
        $this->actingAs($user)
            ->postJson('/api/feedback/store', $payload)
            ->assertOk()
            ->assertJsonPath('duplicate', true);

        $this->assertSame(1, Feedback::count());
    }

    public function test_a_student_cannot_rate_another_students_transaction(): void
    {
        $owner = $this->makeStudent('owner@example.com');
        $request = $this->submitDocumentRequest($owner);

        $this->actingAs($this->makeStudent('other@example.com'))
            ->postJson('/api/feedback/store', [
                'transaction_type' => Feedback::TYPE_DOCUMENT_REQUEST,
                'transaction_id' => $request->id,
                'rating' => 1,
            ])
            ->assertForbidden();

        $this->assertSame(0, Feedback::count());
    }

    public function test_a_rating_must_be_between_one_and_five(): void
    {
        $user = $this->makeStudent();
        $request = $this->submitDocumentRequest($user);

        $this->actingAs($user)
            ->postJson('/api/feedback/store', [
                'transaction_type' => Feedback::TYPE_DOCUMENT_REQUEST,
                'transaction_id' => $request->id,
                'rating' => 0,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('rating');

        $this->assertSame(0, Feedback::count());
    }

    public function test_the_feedback_endpoint_requires_a_signed_in_user(): void
    {
        $this->postJson('/api/feedback/store', [
            'transaction_type' => Feedback::TYPE_DOCUMENT_REQUEST,
            'transaction_id' => 1,
            'rating' => 4,
        ])->assertUnauthorized();
    }
}
