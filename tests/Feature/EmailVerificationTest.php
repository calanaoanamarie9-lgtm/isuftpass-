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

    public function test_the_signed_link_verifies_the_address(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get($this->verificationUrlFor($user))
            ->assertOk()
            ->assertViewIs('auth.email-verified');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_following_the_link_twice_takes_the_user_to_the_dashboard(): void
    {
        $user = User::factory()->unverified()->create();
        $url = $this->verificationUrlFor($user);

        $this->actingAs($user)->get($url)->assertOk();

        $this->actingAs($user->refresh())->get($url)
            ->assertRedirect(route('dashboard', absolute: false));
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
}
