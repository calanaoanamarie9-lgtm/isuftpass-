<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Parents and guests book appointments and nothing else: they have no
 * document-request standing, so no page may offer them one, and - following
 * from the same rule - their sign-up never asks for a name, because no
 * document is ever issued under it.
 *
 * Students and alumni keep both request types and the structured name.
 */
class ParentGuestScopeTest extends TestCase
{
    use RefreshDatabase;

    private function publicUser(string $type, array $attributes = []): User
    {
        return User::factory()->create($attributes + [
            'role' => 'student',
            'registration_type' => $type === 'student' ? null : $type,
            'name' => ucfirst($type).' Account',
            'contact_number' => '09171234567',
        ]);
    }

    private function registrar(): User
    {
        return User::factory()->create(['role' => 'registrar']);
    }

    /* -----------------------------------------------------------------
     | Sign-up: no name for the accounts that never need one
     | ----------------------------------------------------------------- */

    public function test_a_parent_can_register_without_a_name(): void
    {
        Notification::fake();

        $this->post('/register', [
            'email' => 'parent.signup@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'other',
            'registration_type' => 'parent',
        ])->assertRedirect(route('complete-profile', absolute: false));

        $this->assertAuthenticated();

        // The email stands in for the display name, which is never null.
        $this->assertDatabaseHas('users', [
            'email' => 'parent.signup@example.com',
            'name' => 'parent.signup@example.com',
            'registration_type' => 'parent',
            'last_name' => null,
            'first_name' => null,
            'middle_name' => null,
        ]);
    }

    public function test_a_guest_can_register_without_a_name(): void
    {
        Notification::fake();

        $this->post('/register', [
            'email' => 'guest.signup@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'other',
            'registration_type' => 'guest',
        ])->assertRedirect(route('complete-profile', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'guest.signup@example.com',
            'name' => 'guest.signup@example.com',
            'registration_type' => 'guest',
        ]);
    }

    public function test_an_alumni_signup_still_needs_a_name(): void
    {
        $this->post('/register', [
            'email' => 'alumni.signup@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'other',
            'registration_type' => 'alumni',
        ])->assertSessionHasErrors(['last_name', 'first_name', 'middle_name']);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'alumni.signup@example.com']);
    }

    public function test_an_alumni_signup_keeps_its_composed_name(): void
    {
        Notification::fake();

        $this->post('/register', [
            'last_name' => 'Diaz',
            'first_name' => 'Rina',
            'middle_name' => 'Lopez',
            'email' => 'alumni.named@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'other',
            'registration_type' => 'alumni',
        ])->assertRedirect(route('complete-profile', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'alumni.named@example.com',
            'name' => 'Rina Lopez Diaz',
            'last_name' => 'Diaz',
        ]);
    }

    public function test_the_other_signup_form_follows_the_registration_type(): void
    {
        $response = $this->get('/register/form?type=other')->assertOk();

        $html = $response->getContent();

        // One bound block: hidden, unrequired and unsubmitted once Parent or
        // Guest is picked, so the three inputs cannot block the submit.
        $this->assertStringContainsString('x-show="! nameless"', $html);
        $this->assertSame(3, substr_count($html, ':required="! nameless"'));
        $this->assertSame(3, substr_count($html, ':disabled="nameless"'));
        $this->assertStringContainsString("get nameless()", $html);
    }

    public function test_the_student_signup_form_still_demands_a_name(): void
    {
        $response = $this->get('/register/form?type=student')->assertOk();

        $response->assertSee('name="first_name"', false);
        $response->assertDontSee('nameless', false);
    }

    /* -----------------------------------------------------------------
     | What each account type is offered once signed in
     | ----------------------------------------------------------------- */

    public function test_the_request_hub_offers_a_parent_appointments_only(): void
    {
        $this->actingAs($this->publicUser('parent'))
            ->get(route('student.requests.new'))
            ->assertOk()
            ->assertSee('Appointment Request')
            ->assertDontSee('Document Request');
    }

    public function test_the_request_hub_offers_a_guest_appointments_only(): void
    {
        $this->actingAs($this->publicUser('guest'))
            ->get(route('student.requests.new'))
            ->assertOk()
            ->assertSee('Appointment Request')
            ->assertDontSee('Document Request');
    }

    public function test_the_request_hub_offers_a_student_both(): void
    {
        $this->actingAs($this->publicUser('student'))
            ->get(route('student.requests.new'))
            ->assertOk()
            ->assertSee('Appointment Request')
            ->assertSee('Document Request');
    }

    public function test_the_request_hub_offers_an_alumni_both(): void
    {
        $this->actingAs($this->publicUser('alumni'))
            ->get(route('student.requests.new'))
            ->assertOk()
            ->assertSee('Appointment Request')
            ->assertSee('Document Request');
    }

    public function test_parents_and_guests_cannot_open_the_document_request_list(): void
    {
        foreach (['parent', 'guest'] as $type) {
            $this->actingAs($this->publicUser($type))
                ->get(route('student.documents.index'))
                ->assertForbidden();

            $this->actingAs($this->publicUser($type))
                ->get(route('student.documents.create'))
                ->assertForbidden();
        }

        $this->actingAs($this->publicUser('student'))
            ->get(route('student.documents.index'))
            ->assertOk();
    }

    public function test_the_help_page_asks_no_document_questions_of_a_parent_or_guest(): void
    {
        foreach (['parent', 'guest'] as $type) {
            $this->actingAs($this->publicUser($type))
                ->get(route('student.help.index'))
                ->assertOk()
                ->assertSee('How do I book an appointment?')
                ->assertDontSee('How do I request a document?')
                ->assertDontSee('How do I track my document request?');
        }

        $this->actingAs($this->publicUser('student'))
            ->get(route('student.help.index'))
            ->assertOk()
            ->assertSee('How do I request a document?')
            ->assertSee('How do I track my document request?');
    }

    public function test_the_pass_page_hides_document_claim_passes_from_parents_and_guests(): void
    {
        foreach (['parent', 'guest'] as $type) {
            $this->actingAs($this->publicUser($type))
                ->get(route('student.pass.show'))
                ->assertOk()
                ->assertDontSee('Document Request QR Passes')
                ->assertSee('Appointment QR Passes');
        }

        $this->actingAs($this->publicUser('student'))
            ->get(route('student.pass.show'))
            ->assertOk()
            ->assertSee('Document Request QR Passes');
    }

    /* -----------------------------------------------------------------
     | The registrar's own side of the same rule
     | ----------------------------------------------------------------- */

    public function test_the_registrar_document_filter_leaves_out_parents_and_guests(): void
    {
        $this->actingAs($this->registrar())
            ->get(route('registrar.document-requests.index'))
            ->assertOk()
            ->assertSee('<option value="student"', false)
            ->assertSee('<option value="alumni"', false)
            ->assertDontSee('<option value="parent"', false)
            ->assertDontSee('<option value="guest"', false);
    }

    public function test_the_registrar_appointment_filter_still_offers_all_four(): void
    {
        $this->actingAs($this->registrar())
            ->get(route('registrar.appointments.index'))
            ->assertOk()
            ->assertSee('<option value="student"', false)
            ->assertSee('<option value="alumni"', false)
            ->assertSee('<option value="parent"', false)
            ->assertSee('<option value="guest"', false);
    }

    /* -----------------------------------------------------------------
     | The profile card has to stay saveable without a name
     | ----------------------------------------------------------------- */

    public function test_a_nameless_parent_can_still_save_the_profile_card(): void
    {
        $parent = $this->publicUser('parent', [
            'name' => 'nameless.parent@example.com',
            'last_name' => null,
            'first_name' => null,
            'middle_name' => null,
        ]);

        $this->actingAs($parent)
            ->patch(route('profile.update'), [
                'last_name' => '',
                'first_name' => '',
                'middle_name' => '',
                'email' => 'nameless.parent@example.com',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('nameless.parent@example.com', $parent->fresh()->name);
    }
}
