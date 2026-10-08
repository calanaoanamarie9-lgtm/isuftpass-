<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use App\Notifications\RegistrarRequestAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The registrar desk gets its own alert channel: an office-wide row per
 * event, on their own page, opening their own queue. These tests cover the
 * round trip a registrar actually takes — see it in the sidebar, click it,
 * land on the request — rather than only asserting that a row was written.
 */
class RegistrarNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function makeRegistrar(): User
    {
        return User::factory()->create(['role' => 'registrar', 'name' => 'Regina Reyes']);
    }

    private function makeStudentRequest(): \App\Models\DocumentRequest
    {
        $student = User::factory()->create(['role' => 'student', 'name' => 'Delacruz, Juan Miguel']);
        $document = Document::create(['name' => 'Transcript of Records', 'description' => 'TOR', 'fee' => 100.00]);

        $request = $student->documentRequests()->create([
            'student_name' => $student->name,
            'student_address' => 'Iloilo City',
            'student_contact' => '09170000000',
            'student_course_year' => 'BSIT 3',
            'status' => 'submitted',
            'purpose_type' => 'employment',
            'educational_status' => 'not_graduated',
            'educational_level' => 'college',
            'claim_mode' => 'personal',
            'submitted_at' => now(),
        ]);

        $request->documents()->attach($document->id);

        return $request;
    }

    public function test_registrar_sees_the_alert_on_their_notifications_page(): void
    {
        $registrar = $this->makeRegistrar();
        $request = $this->makeStudentRequest();

        $registrar->notify(new RegistrarRequestAlert(
            $request,
            'New document request',
            $request->student_name . ' submitted a request for ' . $request->documentsSummary() . '. It is waiting in your queue.',
        ));

        $this->actingAs($registrar)
            ->get('/registrar/notifications')
            ->assertOk()
            ->assertSee('New document request')
            ->assertSee('waiting in your queue')
            ->assertSee($request->request_number);
    }

    public function test_opening_the_alert_lands_the_registrar_on_the_request(): void
    {
        $registrar = $this->makeRegistrar();
        $request = $this->makeStudentRequest();

        $registrar->notify(new RegistrarRequestAlert($request, 'Payment recorded', 'Paid.'));

        $notification = $registrar->notifications()->first();

        $this->actingAs($registrar)
            ->get('/registrar/notifications/' . $notification->id . '/open')
            ->assertRedirect(route('registrar.document-requests.show', $request));

        // Opening it counts as reading it — the badge has to clear.
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_a_registrar_cannot_open_another_users_notification(): void
    {
        $registrar = $this->makeRegistrar();
        $other = $this->makeRegistrar();
        $request = $this->makeStudentRequest();

        $other->notify(new RegistrarRequestAlert($request, 'New document request', 'Something new.'));

        $notification = $other->notifications()->first();

        $this->actingAs($registrar)
            ->get('/registrar/notifications/' . $notification->id . '/open')
            ->assertForbidden();
    }

    public function test_the_alert_targets_the_registrar_queue_not_the_student_portal(): void
    {
        $registrar = $this->makeRegistrar();
        $request = $this->makeStudentRequest();

        $registrar->notify(new RegistrarRequestAlert($request, 'Payment recorded', 'Paid.'));

        // DatabaseNotification::toArray() returns the stored payload verbatim;
        // it does not re-invoke the notification class.
        $payload = $registrar->notifications()->first()->data;

        // student.documents.show sits behind role:student. A registrar sent
        // there would bounce off a 403 instead of reaching their own queue.
        $this->assertSame(route('registrar.document-requests.show', $request), $payload['url']);
        $this->assertStringNotContainsString('/student/', $payload['url']);
    }
}
