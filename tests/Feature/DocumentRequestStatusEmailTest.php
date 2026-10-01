<?php

namespace Tests\Feature;

use App\Enums\DocumentRequestStatus;
use App\Mail\DocumentRequestCompleted;
use App\Mail\DocumentRequestReadyForPickup;
use App\Mail\DocumentRequestReceived;
use App\Mail\DocumentRequestRejected;
use App\Mail\DocumentRequestStatusUpdate;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Email delivery is off for this deployment (Render's free tier blocks
 * outbound SMTP and no HTTPS mail API is configured), so every document
 * request status change now notifies the student through the in-app
 * notification channel only. These tests keep covering each status
 * transition while pinning down that no mailable leaves the building.
 */
class DocumentRequestStatusEmailTest extends TestCase
{
    use RefreshDatabase;

    private function makeRegistrar(): User
    {
        return User::factory()->create(['role' => 'registrar']);
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

    public function test_advancing_request_does_not_email_student(): void
    {
        Mail::fake();

        [$student, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/next')
            ->assertRedirect();

        $request->refresh();
        $this->assertEquals(DocumentRequestStatus::PROCESSING->value, $request->status);

        Mail::assertNotSent(DocumentRequestStatusUpdate::class);
    }

    public function test_ready_for_pickup_sends_no_email_but_keeps_claiming_details(): void
    {
        Mail::fake();

        [, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::FOR_SIGNATURE->value);

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/next')
            ->assertRedirect();

        $request->refresh();
        $this->assertEquals(DocumentRequestStatus::READY_FOR_PICKUP->value, $request->status);

        Mail::assertNotSent(DocumentRequestReadyForPickup::class);
        Mail::assertNothingSent();
    }

    public function test_completing_request_sends_no_email(): void
    {
        Mail::fake();

        [, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::READY_FOR_PICKUP->value);

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/next')
            ->assertRedirect();

        $request->refresh();
        $this->assertEquals(DocumentRequestStatus::COMPLETED->value, $request->status);
        $this->assertNotNull($request->completed_at);

        Mail::assertNotSent(DocumentRequestCompleted::class);
        Mail::assertNothingSent();
    }

    public function test_ready_for_pickup_uses_registrar_release_date_and_time(): void
    {
        Mail::fake();

        [, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::FOR_SIGNATURE->value);

        $releaseDate = now()->addDays(3)->toDateString();

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/next', [
                'release_date' => $releaseDate,
                'release_time' => '13:30',
            ])
            ->assertRedirect();

        $request->refresh();

        $this->assertEquals(DocumentRequestStatus::READY_FOR_PICKUP->value, $request->status);
        $this->assertEquals($releaseDate, $request->ready_at->toDateString());
        $this->assertEquals('13:30', $request->ready_at->format('H:i'));
    }

    public function test_new_request_does_not_email_student_with_payment_instructions(): void
    {
        Mail::fake();

        $student = User::factory()->create(['role' => 'student']);

        $document = Document::create(['name' => 'Transcript of Records', 'description' => 'TOR', 'fee' => 100.00]);

        $this->actingAs($student)
            ->post('/student/document-requests', [
                'document_ids' => [$document->id],
                'purpose_type' => 'employment',
                'educational_status' => 'not_graduated',
                'educational_level' => 'college',
                'claim_mode' => 'personal',
            ])
            ->assertRedirect(route('student.documents.index'));

        $request = $student->documentRequests()->first();
        $this->assertNotNull($request, 'The document request must still be persisted.');

        Mail::assertNotSent(DocumentRequestReceived::class);
        Mail::assertNothingSent();
    }

    public function test_cancelling_request_sends_no_email_but_records_the_reason(): void
    {
        Mail::fake();

        [, $request] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/cancel', [
                'reason' => 'Incomplete requirements submitted',
            ])
            ->assertRedirect();

        $request->refresh();
        $this->assertEquals(DocumentRequestStatus::CANCELLED->value, $request->status);
        $this->assertEquals('Incomplete requirements submitted', $request->rejection_reason);

        Mail::assertNotSent(DocumentRequestRejected::class);
        Mail::assertNotSent(DocumentRequestStatusUpdate::class);
    }
}
