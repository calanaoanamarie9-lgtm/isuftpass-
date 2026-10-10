<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use App\Notifications\RegistrarRequestAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The header bell is the glance view every role shares: unread count on
 * the icon, the latest alerts in the dropdown, and a link into the full
 * page — rendered on the pages that role actually opens.
 */
class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    private function alertFor(User $user, string $title = 'Payment recorded — ready to release'): void
    {
        $student = User::factory()->create(['role' => 'student']);
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

        $user->notify(new RegistrarRequestAlert($request, $title, 'Something needs your attention.'));
    }

    public function test_student_bell_shows_the_unread_count_and_latest_alert(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $this->alertFor($student, 'Document update for bell test');

        $this->actingAs($student)
            ->get(route('student.appointments.index'))
            ->assertOk()
            ->assertSee('bellOpen')
            ->assertSee('Document update for bell test')
            ->assertSee('1 new')
            ->assertSee(route('student.notifications.index'), false);
    }

    public function test_registrar_bell_lists_latest_alerts_on_their_pages(): void
    {
        $registrar = User::factory()->create(['role' => 'registrar']);
        $this->alertFor($registrar, 'Registrar bell fixture');

        $this->actingAs($registrar)
            ->get(route('registrar.document-requests.index'))
            ->assertOk()
            ->assertSee('bellOpen')
            ->assertSee('Registrar bell fixture')
            ->assertSee(route('registrar.notifications.index'), false);
    }

    public function test_cashier_bell_lists_latest_alerts_on_their_pages(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $this->alertFor($cashier, 'Cashier bell fixture');

        $this->actingAs($cashier)
            ->get(route('cashier.payments.pending'))
            ->assertOk()
            ->assertSee('bellOpen')
            ->assertSee('Cashier bell fixture')
            // The dropdown's own links target the cashier's routes, not
            // the student's or registrar's.
            ->assertSee(route('cashier.notifications.index'), false);
    }

    public function test_the_bell_is_quiet_without_any_notifications(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get(route('student.appointments.index'))
            ->assertOk()
            ->assertSee('bellOpen')
            ->assertSee('No notifications yet.')
            ->assertDontSee('1 new');
    }
}
