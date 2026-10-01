<?php

namespace Tests\Feature;

use App\Mail\DocumentRequestReceived;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Email delivery is off for this deployment: Render's free tier blocks
 * outbound SMTP (ports 25/465/587) and no HTTPS mail API is configured, so
 * no message could ever be delivered. These tests pin down that no code path
 * reaches a mail transport, while the in-app (database) notifications keep
 * working exactly as before.
 */
class EmailSendingDisabledTest extends TestCase
{
    use RefreshDatabase;

    private function makeDocument(): Document
    {
        return Document::create(['name' => 'Transcript of Record (TOR)', 'description' => 'TOR', 'fee' => 100.00]);
    }

    private function createStudent(): User
    {
        $user = User::factory()->create(['role' => 'student', 'name' => 'Juan Dela Cruz']);
        $user->studentProfile()->create([
            'course' => 'BSIT',
            'year_level' => '3rd Year',
            'contact_number' => '09171234567',
            'address' => 'Barangay Uno, Iloilo City',
        ]);

        return $user;
    }

    public function test_document_request_submission_works_without_sending_email(): void
    {
        Mail::fake();

        $document = $this->makeDocument();
        $user = $this->createStudent();

        $response = $this->actingAs($user)->post('/student/document-requests', [
            'document_ids' => [$document->id],
            'purpose_type' => 'employment',
            'educational_status' => 'graduated',
            'educational_level' => 'college',
            'claim_mode' => 'personal',
        ]);

        // The submission is accepted — there is no mail step left to fail it.
        $response->assertRedirect(route('student.documents.index'));

        $request = DocumentRequest::first();
        $this->assertNotNull($request, 'The document request must still be persisted.');
        $this->assertSame($user->id, $request->user_id);

        // The in-app notification that replaces the email must still be written.
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
        ]);

        Mail::assertNothingOutgoing();
    }

    public function test_safe_mailer_never_reaches_the_mail_transport(): void
    {
        Mail::fake();

        $user = $this->createStudent();

        $mailable = new DocumentRequestReceived(DocumentRequest::create([
            'user_id' => $user->id,
            'request_number' => 'REQ-TEST-0001',
            'student_name' => $user->name,
            'purpose_type' => 'employment',
            'educational_status' => 'graduated',
            'educational_level' => 'college',
            'claim_mode' => 'personal',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]));

        // Callers treat true as "handled", so the flow must not change shape.
        $this->assertTrue(\App\Support\SafeMailer::send($user, $mailable));

        Mail::assertNothingOutgoing();
    }

    public function test_forgot_password_never_sends_a_reset_email(): void
    {
        Mail::fake();
        Notification::fake();

        $user = $this->createStudent();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        Notification::assertNotSentTo($user, ResetPassword::class);
        Mail::assertNothingOutgoing();
    }
}
