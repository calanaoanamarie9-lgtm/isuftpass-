<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\DocumentRequest;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The feedback loop, end to end.
 *
 * A finished appointment or document request can be rated 1-5 stars with
 * an optional comment, the submission is stored exactly once, and the
 * System Admin reads both the averages and the individual comments in
 * Feedback Management. The rating window follows the service, not the
 * booking: nothing is offered while a request is still in progress, and
 * an unfinished visit never shows a rate link.
 */
class FeedbackManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function student(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    private function completedAppointment(User $owner): Appointment
    {
        return $owner->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Enrollment',
            'date' => now()->toDateString(),
            'time_slot' => '09:00 AM - 10:00 AM',
            'status' => AppointmentStatus::COMPLETED->value,
        ]);
    }

    private function documentRequest(User $owner, string $status = 'completed'): DocumentRequest
    {
        return $owner->documentRequests()->create([
            'student_name' => 'Maria Santos',
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
    }

    /*
    |--------------------------------------------------------------------------
    | Feedback Management (admin)
    |--------------------------------------------------------------------------
    */

    public function test_the_admin_reads_the_comments_in_feedback_management(): void
    {
        $student = $this->student();
        Feedback::recordFor(
            $student,
            $this->completedAppointment($student),
            5,
            'The registrar was quick and polite.'
        );

        $this->actingAs($this->admin())
            ->get('/admin/feedback')
            ->assertOk()
            ->assertSee('Feedback Management')
            ->assertSee('The registrar was quick and polite.')
            ->assertSee($student->name);
    }

    public function test_the_summary_shows_the_average_and_the_distribution(): void
    {
        $student = $this->student();

        // 5, 5 and 1 — an average of 3.7 and a distribution of two fives
        // and one one, so the numbers on the page are all distinguishable.
        Feedback::recordFor($student, $this->completedAppointment($student), 5, null);
        Feedback::recordFor($student, $this->completedAppointment($student), 5, null);
        Feedback::recordFor($student, $this->documentRequest($student), 1, 'Too slow.');

        $this->actingAs($this->admin())
            ->get('/admin/feedback')
            ->assertOk()
            ->assertSee('3.7')
            ->assertSee('(67%)')
            ->assertSee('(33%)')
            ->assertSee('Too slow.')
            ->assertSee('By Transaction');
    }

    public function test_feedback_management_is_admins_only(): void
    {
        $this->actingAs($this->student())
            ->get('/admin/feedback')
            ->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => 'registrar']))
            ->get('/admin/feedback')
            ->assertForbidden();
    }

    public function test_the_admin_can_filter_the_list_by_rating(): void
    {
        $student = $this->student();
        Feedback::recordFor($student, $this->completedAppointment($student), 5, 'Fast and friendly.');
        Feedback::recordFor($student, $this->documentRequest($student), 1, 'Long waiting time.');

        $this->actingAs($this->admin())
            ->get('/admin/feedback?rating=1')
            ->assertOk()
            ->assertSee('Long waiting time.')
            ->assertDontSee('Fast and friendly.');

        $this->actingAs($this->admin())
            ->get('/admin/feedback?rating=5')
            ->assertOk()
            ->assertSee('Fast and friendly.')
            ->assertDontSee('Long waiting time.');
    }

    public function test_the_admin_can_filter_the_list_by_transaction(): void
    {
        $student = $this->student();
        Feedback::recordFor($student, $this->completedAppointment($student), 5, 'Smooth booking.');
        Feedback::recordFor($student, $this->documentRequest($student), 4, 'Document was ready.');

        $this->actingAs($this->admin())
            ->get('/admin/feedback?type='.Feedback::TYPE_APPOINTMENT)
            ->assertOk()
            ->assertSee('Smooth booking.')
            ->assertDontSee('Document was ready.');

        $this->actingAs($this->admin())
            ->get('/admin/feedback?type='.Feedback::TYPE_DOCUMENT_REQUEST)
            ->assertOk()
            ->assertSee('Document was ready.')
            ->assertDontSee('Smooth booking.');
    }

    public function test_the_page_is_calm_when_nothing_has_been_rated(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/feedback')
            ->assertOk()
            ->assertSee('No Feedback Yet')
            ->assertSee('0 ratings recorded');
    }

    /*
    |--------------------------------------------------------------------------
    | The student's way in
    |--------------------------------------------------------------------------
    */

    public function test_a_finished_document_request_offers_the_rate_form(): void
    {
        $student = $this->student();
        $request = $this->documentRequest($student, 'ready_for_pickup');

        $this->actingAs($student)
            ->get('/student/document-requests/'.$request->id)
            ->assertOk()
            ->assertSee('Rate This Service')
            ->assertSee(route('student.feedback.create', ['kind' => 'document', 'id' => $request->id]));
    }

    public function test_a_request_still_in_progress_does_not_offer_rating(): void
    {
        $student = $this->student();
        $request = $this->documentRequest($student, 'processing');

        $this->actingAs($student)
            ->get('/student/document-requests/'.$request->id)
            ->assertOk()
            ->assertDontSee('Rate This Service')
            ->assertDontSee(route('student.feedback.create', ['kind' => 'document', 'id' => $request->id]));
    }

    public function test_a_completed_appointment_offers_the_rate_form(): void
    {
        $student = $this->student();
        $appointment = $this->completedAppointment($student);

        $this->actingAs($student)
            ->get('/student/appointments?tab=completed')
            ->assertOk()
            ->assertSee('Rate this service')
            ->assertSee(route('student.feedback.create', ['kind' => 'appointment', 'id' => $appointment->id]));
    }

    public function test_a_rated_request_shows_the_rating_instead_of_the_form(): void
    {
        $student = $this->student();
        $request = $this->documentRequest($student);
        Feedback::recordFor($student, $request, 5, 'Very helpful staff.');

        $this->actingAs($student)
            ->get('/student/document-requests/'.$request->id)
            ->assertOk()
            ->assertSee('Your Rating')
            ->assertSee('Thank you for your feedback!')
            ->assertSee('Very helpful staff.')
            ->assertDontSee('Rate this service');
    }

    public function test_a_rated_appointment_shows_the_rating_instead_of_the_form(): void
    {
        $student = $this->student();
        $appointment = $this->completedAppointment($student);
        Feedback::recordFor($student, $appointment, 4, null);

        $this->actingAs($student)
            ->get('/student/appointments?tab=completed')
            ->assertOk()
            ->assertSee('Thank you for your feedback!')
            ->assertDontSee('Rate this service');
    }

    /*
    |--------------------------------------------------------------------------
    | Submitting
    |--------------------------------------------------------------------------
    */

    public function test_submitting_the_form_saves_the_rating_and_comment(): void
    {
        $student = $this->student();
        $request = $this->documentRequest($student);

        $this->actingAs($student)
            ->get('/student/feedback/create?kind=document&id='.$request->id)
            ->assertOk()
            ->assertSee('Submit Feedback');

        $this->actingAs($student)
            ->post('/student/feedback', [
                'kind' => 'document',
                'id' => $request->id,
                'rating' => 4,
                'comment' => 'Fast and friendly.',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $student->id,
            'feedbackable_id' => $request->id,
            'transaction_type' => Feedback::TYPE_DOCUMENT_REQUEST,
            'rating' => 4,
            'comment' => 'Fast and friendly.',
        ]);
    }

    public function test_a_service_can_only_be_rated_once(): void
    {
        $student = $this->student();
        $request = $this->documentRequest($student);

        $payload = [
            'kind' => 'document',
            'id' => $request->id,
            'rating' => 5,
            'comment' => 'First and only.',
        ];

        $this->actingAs($student)->post('/student/feedback', $payload);
        $this->actingAs($student)->post('/student/feedback', $payload);

        $this->assertSame(1, Feedback::count());
    }

    public function test_rating_is_refused_before_the_service_is_finished(): void
    {
        $student = $this->student();
        $request = $this->documentRequest($student, 'processing');

        $this->actingAs($student)
            ->get('/student/feedback/create?kind=document&id='.$request->id)
            ->assertForbidden();

        $this->actingAs($student)
            ->post('/student/feedback', [
                'kind' => 'document',
                'id' => $request->id,
                'rating' => 5,
            ])
            ->assertForbidden();

        $this->assertSame(0, Feedback::count());
    }
}
