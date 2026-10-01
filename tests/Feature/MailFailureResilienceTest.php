<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Regression guard for the production 500 seen on Render when SMTP
 * credentials were missing: the request row is written *before* the email
 * goes out, so a transport failure must never surface as a server error.
 */
class MailFailureResilienceTest extends TestCase
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

    public function test_document_request_submission_survives_smtp_failure(): void
    {
        $document = $this->makeDocument();
        $user = $this->createStudent();

        // Simulate Render with no MAIL_USERNAME / MAIL_PASSWORD set.
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP connection could not be established'));

        $response = $this->actingAs($user)->post('/student/document-requests', [
            'document_ids' => [$document->id],
            'purpose_type' => 'employment',
            'educational_status' => 'graduated',
            'educational_level' => 'college',
            'claim_mode' => 'personal',
        ]);

        // Not a 500, not a redirect back with errors — the request is accepted.
        $response->assertRedirect(route('student.documents.index'));

        $request = \App\Models\DocumentRequest::first();
        $this->assertNotNull($request, 'The document request must still be persisted.');
        $this->assertSame($user->id, $request->user_id);

        // The database notification that follows the mail call must still run.
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
        ]);
    }

    public function test_safe_mailer_returns_false_instead_of_throwing(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $document = $this->makeDocument();
        $user = $this->createStudent();

        $mailable = new \App\Mail\DocumentRequestReceived(
            \App\Models\DocumentRequest::create([
                'user_id' => $user->id,
                'request_number' => 'REQ-TEST-0001',
                'student_name' => $user->name,
                'purpose_type' => 'employment',
                'educational_status' => 'graduated',
                'educational_level' => 'college',
                'claim_mode' => 'personal',
                'status' => 'submitted',
                'submitted_at' => now(),
            ])
        );

        $this->assertFalse(\App\Support\SafeMailer::send($user, $mailable));
    }

    public function test_forgot_password_form_survives_smtp_failure(): void
    {
        $user = $this->createStudent();

        // The password reset goes through Laravel's notification pipeline
        // rather than Mail::to(), so it needs its own guard.
        \Illuminate\Support\Facades\Password::shouldReceive('sendResetLink')
            ->andThrow(new \RuntimeException('SMTP connection could not be established'));

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }
}
