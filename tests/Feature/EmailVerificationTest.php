<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use App\Support\SafeMailer;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build the same URL VerifyEmailNotification puts in the inbox.
     */
    private function verificationUrlFor(User $user, ?string $hash = null): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->getKey(),
            'hash' => $hash ?? sha1($user->getEmailForVerification()),
        ]);
    }

    public function test_the_verification_prompt_renders_for_an_unverified_user(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get('/email/verify')
            ->assertOk()
            ->assertSee('Verify Your Email');
    }

    public function test_the_prompt_sends_an_already_verified_user_to_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/email/verify')
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_guests_cannot_reach_the_prompt(): void
    {
        User::factory()->unverified()->create();

        $this->get('/email/verify')->assertRedirect(route('login', absolute: false));
    }

    public function test_registering_leaves_the_address_unverified_and_sends_the_link(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'New Student',
            'email' => 'newstudent@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'student',
        ]);

        $response->assertRedirect(route('complete-profile', absolute: false));

        $user = User::query()->where('email', 'newstudent@example.com')->firstOrFail();

        $this->assertNull($user->email_verified_at, 'A fresh registration must not claim a verified address.');

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_the_signed_link_verifies_the_address_and_signs_the_holder_in(): void
    {
        $user = User::factory()->unverified()->create();

        // No actingAs: the inbox usually lives on the phone, a device with
        // no session. The link itself must carry the holder in - requirement
        // of the flow, no login page in between.
        $this->get($this->verificationUrlFor($user))
            ->assertRedirect(route('complete-profile', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_following_the_link_twice_keeps_the_user_verified_and_signed_in(): void
    {
        $user = User::factory()->unverified()->create();
        $url = $this->verificationUrlFor($user);

        $this->get($url)->assertRedirect(route('complete-profile', absolute: false));

        // Close the session entirely - the second click is another device
        // with nothing cached, and it must work the same way.
        $this->post('/logout');

        $this->get($url)->assertRedirect(route('complete-profile', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_a_pending_office_account_is_turned_back_to_the_login_page(): void
    {
        $user = User::factory()->unverified()->create([
            'role' => User::ROLE_OFFICE,
            'approval_status' => User::APPROVAL_PENDING,
        ]);

        // Signing the holder in is only safe because the same gates the
        // login form applies are re-checked here. A signed link alone must
        // not be a way past the approval desk.
        $this->get($this->verificationUrlFor($user))
            ->assertRedirect(route('login', absolute: false))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertNull($user->refresh()->email_verified_at, 'A signed link must not verify what the login form would refuse.');
    }

    public function test_a_rejected_office_account_is_turned_back_to_the_login_page(): void
    {
        $user = User::factory()->unverified()->create([
            'role' => User::ROLE_OFFICE,
            'approval_status' => User::APPROVAL_REJECTED,
            'rejection_reason' => 'Incomplete requirements.',
        ]);

        $this->get($this->verificationUrlFor($user))
            ->assertRedirect(route('login', absolute: false))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertNull($user->refresh()->email_verified_at);
    }

    public function test_a_deactivated_account_is_turned_back_to_the_login_page(): void
    {
        $user = User::factory()->unverified()->create(['is_active' => false]);

        $this->get($this->verificationUrlFor($user))
            ->assertRedirect(route('login', absolute: false))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertNull($user->refresh()->email_verified_at);
    }

    public function test_an_unsigned_link_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get('/email/verify/'.$user->getKey().'/'.sha1($user->getEmailForVerification()))
            ->assertForbidden();

        $this->assertNull($user->refresh()->email_verified_at);
    }

    public function test_a_link_pointing_at_a_different_address_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();

        // The hash is only sha1(email), so it is trivial to recompute. The
        // signature has to cover *this* exact URL for the check to hold.
        $this->actingAs($user)
            ->get($this->verificationUrlFor($user, 'not-the-right-hash'))
            ->assertForbidden();

        $this->assertNull($user->refresh()->email_verified_at);
    }

    public function test_resend_reports_success(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->from('/email/verify')
            ->post('/email/verification-notification')
            ->assertRedirect('/email/verify')
            ->assertSessionHas('status', 'verification-link-sent');

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_resend_admits_failure_when_the_provider_is_down(): void
    {
        config(['mail.default' => 'offline']);

        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->from('/email/verify')
            ->post('/email/verification-notification')
            ->assertRedirect('/email/verify')
            ->assertSessionHas('status', 'verification-link-failed');
    }

    public function test_a_provider_outage_never_breaks_registration(): void
    {
        config(['mail.default' => 'offline']);

        $response = $this->post('/register', [
            'name' => 'Outage Student',
            'email' => 'outage@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'student',
        ]);

        $response->assertRedirect(route('complete-profile', absolute: false));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'outage@example.com']);
        $this->assertNull(
            User::query()->where('email', 'outage@example.com')->firstOrFail()->email_verified_at
        );
    }

    public function test_safe_mailer_swallows_a_throwing_notification(): void
    {
        $user = new class implements MustVerifyEmail
        {
            public string $email = 'boom@example.com';

            public function hasVerifiedEmail()
            {
                return false;
            }

            public function markEmailAsVerified()
            {
                return false;
            }

            public function sendEmailVerificationNotification()
            {
                throw new \RuntimeException('provider down');
            }

            public function getEmailForVerification()
            {
                return $this->email;
            }
        };

        $this->assertFalse(SafeMailer::verifyEmail($user));
    }

    public function test_safe_mailer_reports_success(): void
    {
        $user = new class implements MustVerifyEmail
        {
            public bool $sent = false;

            public string $email = 'ok@example.com';

            public function hasVerifiedEmail()
            {
                return false;
            }

            public function markEmailAsVerified()
            {
                return true;
            }

            public function sendEmailVerificationNotification()
            {
                $this->sent = true;
            }

            public function getEmailForVerification()
            {
                return $this->email;
            }
        };

        $this->assertTrue(SafeMailer::verifyEmail($user));
        $this->assertTrue($user->sent);
    }

    // --- The gate ------------------------------------------------------------
    // Registration hands off to complete-profile. These cases are what makes
    // that handoff a wall rather than a suggestion.

    public function test_an_unverified_user_cannot_reach_complete_profile(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get('/complete-profile')
            ->assertRedirect(route('verification.notice', absolute: false));

        $this->actingAs($user)
            ->post('/complete-profile', ['contact_number' => '09171234567'])
            ->assertRedirect(route('verification.notice', absolute: false));

        $this->assertNull($user->refresh()->contact_number, 'The form must not save behind the gate.');
    }

    public function test_an_unverified_user_cannot_reach_a_dashboard(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('verification.notice', absolute: false));
    }

    public function test_a_fresh_signup_is_turned_away_from_the_profile_form(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Gated Student',
            'email' => 'gated@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'student',
        ]);

        $user = User::query()->where('email', 'gated@example.com')->firstOrFail();

        $this->actingAs($user)
            ->get('/complete-profile')
            ->assertRedirect(route('verification.notice', absolute: false));

        $this->actingAs($user)
            ->get('/email/verify')
            ->assertOk();
    }

    public function test_an_unverified_user_can_still_log_out(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->post('/logout');

        // The gate walls off dashboards, never the way out - otherwise a
        // user with an unreachable inbox would be trapped in the session.
        $this->assertGuest();
    }

    public function test_a_verified_user_walks_straight_through(): void
    {
        // role is NOT NULL DEFAULT 'student' in production, so this is the
        // shape of every account that will ever reach these two routes.
        // The contact number is what makes the profile "complete" - the
        // dashboard guard (The profile guard, below) would otherwise send
        // the second request back to the form.
        $user = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'contact_number' => '09170001111',
        ]);

        $this->actingAs($user)->get('/complete-profile')->assertOk();
        $this->actingAs($user)->get('/dashboard')->assertOk();
    }

    // --- Where verification leaves you ---------------------------------------
    // Straight into the next step: the personal-details form while details
    // are missing, the dashboard once they exist. Never a login page.

    public function test_verifying_continues_to_the_profile_form_when_details_are_missing(): void
    {
        $user = User::factory()->unverified()->create();

        $this->get($this->verificationUrlFor($user))
            ->assertRedirect(route('complete-profile', absolute: false));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_verifying_continues_to_the_dashboard_once_details_exist(): void
    {
        $user = User::factory()->unverified()->create(['contact_number' => '09170001111']);

        $this->get($this->verificationUrlFor($user))
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_verification_returns_to_where_the_gate_interrupted_once_details_exist(): void
    {
        $user = User::factory()->unverified()->create(['contact_number' => '09170001111']);

        // This is the bounce EnsureEmailIsVerified performs; the link click
        // must land back on the interrupted URL, not on a default.
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('verification.notice', absolute: false));

        $this->actingAs($user->refresh())
            ->get($this->verificationUrlFor($user))
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_verification_sends_missing_details_on_to_the_form_instead_of_the_interrupted_dashboard(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('verification.notice', absolute: false));

        // The dashboard would only bounce them on to the form anyway (see
        // The profile guard) - hand off directly instead of taking the
        // detour. Requirement: verification goes to the form, not past it.
        $this->actingAs($user->refresh())
            ->get($this->verificationUrlFor($user))
            ->assertRedirect(route('complete-profile', absolute: false));
    }

    // --- The profile guard ----------------------------------------------------
    // The dashboard route is the end of the onboarding road only once the
    // details exist; until then it hands the user back to the form, and the
    // form hands them straight back to the dashboard when it succeeds.
    // Staff accounts never saw a registration form, so the step does not
    // exist for them and they go straight through.

    public function test_the_dashboard_sends_an_incomplete_profile_back_to_the_form(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('complete-profile', absolute: false));
    }

    public function test_submitting_the_details_lands_straight_on_the_dashboard(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($user)
            ->post('/complete-profile', ['contact_number' => '09171234567'])
            ->assertRedirect(route('dashboard', absolute: false));

        // The guard must let them through on the very next request - no
        // second round trip, no bounce back to the form.
        $this->get('/dashboard')->assertOk();
    }

    public function test_staff_accounts_reach_the_dashboard_without_the_profile_step(): void
    {
        // No contact number was ever submitted by either of these - staff
        // are seeded or admin-created, never registered, so the
        // personal-details step does not exist for them.
        $this->actingAs(User::factory()->create(['role' => User::ROLE_REGISTRAR]))
            ->get('/dashboard')
            ->assertOk();

        $this->actingAs(User::factory()->create(['role' => User::ROLE_ADMIN]))
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_verifying_sends_a_staff_account_straight_to_the_dashboard(): void
    {
        $user = User::factory()->unverified()->create(['role' => User::ROLE_REGISTRAR]);

        $this->get($this->verificationUrlFor($user))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }
}
