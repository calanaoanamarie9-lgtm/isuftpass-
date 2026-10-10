<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

/**
 * "Continue with Google": the round trip Socialite runs, and the account
 * rules it must keep. Google proves the ADDRESS, not the eligibility -
 * so the emailed-link step is replaced (verified on arrival) while the
 * login form's pending / rejected / deactivated gates, the profile
 * completion step and the link-instead-of-duplicate rule all still apply.
 */
class GoogleSignInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);
    }

    /**
     * Stand in for the Google round trip: the provider the facade hands
     * out returns one canned account instead of talking to Google.
     * Facade::swap, not just a container rebind — Socialite memoizes its
     * resolved root, so a second fake in the same test would otherwise
     * keep handing out the first provider.
     */
    private function fakeGoogle(string $email, string $name = 'Google User'): void
    {
        $googleUser = Mockery::mock(SocialiteUser::class);
        $googleUser->shouldReceive('getEmail')->andReturn($email);
        $googleUser->shouldReceive('getName')->andReturn($name);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($googleUser);

        $manager = Mockery::mock(SocialiteFactory::class);
        $manager->shouldReceive('driver')->with('google')->andReturn($provider);

        $this->app->instance(SocialiteFactory::class, $manager);
        Socialite::clearResolvedInstances();
    }

    public function test_google_redirect_sends_the_visitor_to_the_consent_screen(): void
    {
        $consent = 'https://accounts.google.com/o/oauth2/auth?client_id=test-client-id';

        $provider = Mockery::mock();
        $provider->shouldReceive('redirect')->andReturn(redirect($consent));

        $this->mock(SocialiteFactory::class)
            ->shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('google.redirect'))->assertRedirect($consent);
    }

    public function test_first_signin_creates_a_verified_student_and_lands_on_complete_profile(): void
    {
        $this->fakeGoogle('gina.google@gmail.com', 'Gina Google');

        $this->get(route('google.callback'))
            ->assertRedirect(route('complete-profile', absolute: false));

        $user = User::query()->where('email', 'gina.google@gmail.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);

        // Google vouched for the address, so no emailed link is needed.
        $this->assertNotNull($user->email_verified_at);

        // The same shape a student signup creates - the personal-details
        // form and the QR pass both hang off these columns.
        $this->assertSame(User::ROLE_STUDENT, $user->role);
        $this->assertNull($user->registration_type);
        $this->assertNotNull($user->studentProfile);
        $this->assertNotNull($user->studentProfile->pass_token);
        $this->assertSame('Gina Google', $user->name);
    }

    public function test_an_existing_account_is_linked_never_duplicated(): void
    {
        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'role' => User::ROLE_STUDENT,
            'email_verified_at' => now(),
            'contact_number' => '09171234567',
        ]);

        $this->fakeGoogle('jane@example.com', 'Jane From Google');

        $this->get(route('google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('users', 1);
        // The name the account was registered with is kept - Google only
        // supplies the verified address, not a rename.
        $this->assertSame($user->name, $user->fresh()->name);
    }

    public function test_signing_in_through_google_marks_a_stale_account_verified(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'late.verify@example.com',
            'contact_number' => '09171234567',
        ]);

        $this->fakeGoogle('late.verify@example.com');

        $this->get(route('google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_an_account_without_details_still_goes_through_complete_profile(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'no.details@example.com',
            'role' => User::ROLE_STUDENT,
        ]);

        $this->fakeGoogle('no.details@example.com');

        $this->get(route('google.callback'))
            ->assertRedirect(route('complete-profile', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_staff_account_signs_in_straight_to_its_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'registrardingle@isufst.edu.ph',
            'role' => User::ROLE_REGISTRAR,
            'email_verified_at' => now(),
        ]);

        $this->fakeGoogle('registrardingle@isufst.edu.ph');

        $this->get(route('google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_pending_office_applicant_cannot_sign_in_through_google(): void
    {
        User::factory()->create([
            'email' => 'pending.office@example.com',
            'role' => User::ROLE_OFFICE,
            'approval_status' => User::APPROVAL_PENDING,
        ]);

        $this->fakeGoogle('pending.office@example.com');

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertStringContainsString('pending', session('errors')->first('email'));
    }

    public function test_a_rejected_office_applicant_cannot_sign_in_through_google(): void
    {
        User::factory()->create([
            'email' => 'rejected.office@example.com',
            'role' => User::ROLE_OFFICE,
            'approval_status' => User::APPROVAL_REJECTED,
            'rejection_reason' => 'Duplicate application',
            'is_active' => false,
        ]);

        $this->fakeGoogle('rejected.office@example.com');

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertStringContainsString('Duplicate application', session('errors')->first('email'));
    }

    public function test_a_deactivated_account_cannot_sign_in_through_google(): void
    {
        User::factory()->create([
            'email' => 'deactivated@example.com',
            'is_active' => false,
        ]);

        $this->fakeGoogle('deactivated@example.com');

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertStringContainsString('deactivated', session('errors')->first('email'));
    }

    public function test_the_buttons_render_only_while_the_client_is_configured(): void
    {
        config(['services.google.client_id' => null]);

        $this->get('/login')->assertOk()->assertDontSee('Continue with Google');
        $this->get('/register')->assertOk()->assertDontSee('Continue with Google');

        config(['services.google.client_id' => 'test-client-id']);

        $this->get('/login')
            ->assertOk()
            ->assertSee('Continue with Google')
            ->assertSee(route('google.redirect'), false);

        $this->get('/register')
            ->assertOk()
            ->assertSee('Continue with Google')
            ->assertSee(route('google.redirect'), false);
    }

    public function test_an_unconfigured_deployment_refuses_the_oauth_routes(): void
    {
        config(['services.google.client_id' => null]);

        $response = $this->get(route('google.redirect'));

        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHasErrors('email');

        $response = $this->get(route('google.callback'));

        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
