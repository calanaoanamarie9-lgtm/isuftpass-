<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * What a registration actually buys each account type: the full walk from
 * "Create Account" through the emailed link and the personal-details form
 * to the dashboard built for that type.
 *
 * EmailVerificationTest pins each gate with factory users; this class starts
 * at the real register button so the wiring between the four steps cannot
 * drift apart unnoticed - student, and the three "Other" types (alumni,
 * guest, parent) that land on dashboards of their own.
 */
class OnboardingJourneyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The same URL VerifyEmailNotification puts in the inbox.
     */
    private function verificationUrlFor(User $user): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]);
    }

    /**
     * Register as one account type, sit at the verification prompt, click
     * the emailed link, submit the personal-details form, and land on
     * $dashboardView with $banner on screen.
     */
    private function walkJourney(array $registration, array $details, string $dashboardView, string $banner): User
    {
        Notification::fake();

        $this->post('/register', $registration + [
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('complete-profile', absolute: false));

        $user = User::query()->where('email', $registration['email'])->firstOrFail();

        // Still unverified: the details form sits behind the verification prompt.
        $this->get('/complete-profile')
            ->assertRedirect(route('verification.notice', absolute: false));

        // The emailed link keeps the session and continues to the form itself.
        $this->get($this->verificationUrlFor($user))
            ->assertRedirect(route('complete-profile', absolute: false));

        $this->assertAuthenticatedAs($user);

        $this->get('/complete-profile')
            ->assertOk()
            ->assertSee('Complete Personal Details');

        // Submitting the details lands straight on this type's dashboard.
        $this->post('/complete-profile', $details)
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertFalse($user->fresh()->requiresProfileCompletion());

        $this->get('/dashboard')
            ->assertOk()
            ->assertViewIs($dashboardView)
            ->assertSee($banner);

        return $user;
    }

    public function test_a_student_walks_from_register_to_the_student_dashboard(): void
    {
        $this->walkJourney(
            [
                'name' => 'Jane Student',
                'email' => 'jane.journey@example.com',
                'user_type' => 'student',
            ],
            [
                'student_id' => '2024-12345',
                'course' => 'BSIS',
                'year_level' => '2nd Year',
                'contact_number' => '09171234567',
                'address' => 'Barotac Nuevo, Iloilo',
            ],
            'student.dashboard',
            'Welcome, Jane Student!',
        );
    }

    public function test_an_alumni_walks_from_register_to_the_alumni_dashboard(): void
    {
        $user = $this->walkJourney(
            [
                'name' => 'Anna Alumni',
                'email' => 'anna.journey@example.com',
                'user_type' => 'other',
                'registration_type' => 'alumni',
            ],
            [
                'contact_number' => '09171234567',
                'student_id' => '2019-54321',
                'course' => 'BSA',
                'year_graduated' => '2024',
            ],
            'alumni.dashboard',
            'Welcome, Anna Alumni!',
        );

        $this->assertSame('alumni', $user->fresh()->registration_type);
    }

    public function test_a_guest_walks_from_register_to_the_guest_dashboard(): void
    {
        $user = $this->walkJourney(
            [
                'name' => 'Gina Guest',
                'email' => 'gina.journey@example.com',
                'user_type' => 'other',
                'registration_type' => 'guest',
            ],
            [
                'contact_number' => '09171234567',
                'purpose' => 'visit',
            ],
            'guest.dashboard',
            'ISUFSTPASS Guest Portal',
        );

        $this->assertSame('guest', $user->fresh()->registration_type);

        // What a guest can do: the visit menu. The student document-request
        // menu must stay out of reach - the row's role is still 'student',
        // so roleRule() is what surfaces registration_type instead.
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Book a Visit')
            ->assertDontSee('New Request');
    }

    public function test_a_parent_walks_from_register_to_the_parent_dashboard(): void
    {
        $user = $this->walkJourney(
            [
                'name' => 'Pia Parent',
                'email' => 'pia.journey@example.com',
                'user_type' => 'other',
                'registration_type' => 'parent',
            ],
            [
                'contact_number' => '09171234567',
                'relationship_to_student' => 'mother',
                'student_full_name' => 'Jane Student',
            ],
            'parent.dashboard',
            'Parent / Guardian Portal',
        );

        $this->assertSame('parent', $user->fresh()->registration_type);
    }
}
