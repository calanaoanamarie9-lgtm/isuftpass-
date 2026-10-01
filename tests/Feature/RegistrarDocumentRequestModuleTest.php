<?php

namespace Tests\Feature;

use App\Enums\DocumentRequestStatus;
use App\Mail\DocumentRequestReadyForPickup;
use App\Models\Appointment;
use App\Models\Document;
use App\Models\User;
use App\Notifications\DocumentRequestStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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
        ]);
        $request->documents()->attach($document->id);

        return [$student, $request];
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

    public function test_registrar_can_view_request_details(): void
    {
        [$student] = $this->makeStudentWithRequest();

        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/document-requests/' . $student->documentRequests()->first()->id)
            ->assertOk()
            ->assertSee('Document Details')
            ->assertSee($student->name);
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

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/next')
            ->assertRedirect();

        $request->refresh();
        $this->assertEquals(DocumentRequestStatus::PROCESSING->value, $request->status);
        $this->assertNotNull($request->processing_at);

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/next');

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/next');

        $request->refresh();
        $this->assertEquals(DocumentRequestStatus::READY_FOR_PICKUP->value, $request->status);

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/next');

        $request->refresh();
        $this->assertEquals(DocumentRequestStatus::COMPLETED->value, $request->status);
        $this->assertNotNull($request->completed_at);

        $this->assertEquals(4, $student->notifications()->count());
        $this->assertEquals(DocumentRequestStatusNotification::class, $student->notifications()->first()->type);
    }

    public function test_completed_request_cannot_be_advanced(): void
    {
        [, $request] = $this->makeStudentWithRequest(DocumentRequestStatus::COMPLETED->value);

        $this->actingAs($this->makeRegistrar())
            ->post('/registrar/document-requests/' . $request->id . '/next')
            ->assertNotFound();
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
            ->assertSee('Verify & Claim / Release Documents');
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
            ->assertSee('No student found');
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