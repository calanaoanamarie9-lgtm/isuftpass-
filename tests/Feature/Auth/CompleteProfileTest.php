<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompleteProfileTest extends TestCase
{
    use RefreshDatabase;

    private function registerStudent(string $email = 'jane@example.com'): User
    {
        $this->post('/register', [
            'name' => 'Jane Student',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'student',
        ]);

        return $this->verified(auth()->user());
    }

    private function registerOther(string $registrationType, string $email = 'john@example.com'): User
    {
        $this->post('/register', [
            'name' => 'John Other',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'other',
            'registration_type' => $registrationType,
        ]);

        return $this->verified(auth()->user());
    }

    /**
     * A fresh signup is turned away from this form until the emailed link is
     * clicked - that gate has its own cases in EmailVerificationTest. These
     * cases are about validation, so step past it the way the link would.
     */
    private function verified(User $user): User
    {
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    public function test_student_can_complete_personal_details(): void
    {
        $this->registerStudent();

        $this->get('/complete-profile')
            ->assertOk()
            ->assertSee('Complete Personal Details')
            ->assertSee('Student ID');

        $this->post('/complete-profile', [
            'student_id' => '2024-12345',
            'course' => 'BSIS',
            'year_level' => '2nd Year',
            'contact_number' => '09171234567',
            'address' => 'Barotac Nuevo, Iloilo',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('student_profiles', [
            'student_id' => '2024-12345',
            'course' => 'BSIS',
            'year_level' => '2nd Year',
            'contact_number' => '09171234567',
            'address' => 'Barotac Nuevo, Iloilo',
        ]);
    }

    public function test_student_requires_contact_number(): void
    {
        $this->registerStudent();

        $this->post('/complete-profile', [
            'student_id' => '2024-12345',
        ])->assertSessionHasErrors('contact_number');
    }

    public function test_alumni_can_complete_personal_details(): void
    {
        $this->registerOther('alumni');

        $this->get('/complete-profile')
            ->assertOk()
            ->assertSee('Alumni Details')
            ->assertSee('Year Graduated');

        $this->post('/complete-profile', [
            'contact_number' => '09171234567',
            'student_id' => '2019-54321',
            'course' => 'BSA',
            'year_graduated' => '2024',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'contact_number' => '09171234567',
            'student_id' => '2019-54321',
            'course' => 'BSA',
            'year_graduated' => '2024',
        ]);
    }

    public function test_alumni_requires_year_graduated(): void
    {
        $this->registerOther('alumni');

        $this->post('/complete-profile', [
            'contact_number' => '09171234567',
        ])->assertSessionHasErrors('year_graduated');
    }

    public function test_guest_can_complete_personal_details(): void
    {
        $this->registerOther('guest');

        $this->get('/complete-profile')
            ->assertOk()
            ->assertSee('Visitor Information')
            ->assertSee('Purpose for Using ISUFSTPASS');

        $this->post('/complete-profile', [
            'contact_number' => '09171234567',
            'organization' => 'DICT Region VI',
            'address' => 'Iloilo City',
            'purpose' => 'inquiry',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'contact_number' => '09171234567',
            'organization' => 'DICT Region VI',
            'address' => 'Iloilo City',
            'purpose' => 'inquiry',
        ]);
    }

    public function test_parent_can_complete_personal_details(): void
    {
        $this->registerOther('parent');

        $this->get('/complete-profile')
            ->assertOk()
            ->assertSee('Parent / Guardian Information')
            ->assertSee('Student Information');

        $this->post('/complete-profile', [
            'contact_number' => '09171234567',
            'relationship_to_student' => 'mother',
            'student_full_name' => 'Jane Student',
            'student_id' => '2024-12345',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'contact_number' => '09171234567',
            'relationship_to_student' => 'mother',
            'student_full_name' => 'Jane Student',
            'student_id' => '2024-12345',
        ]);
    }

    public function test_requires_guest_purpose(): void
    {
        $this->registerOther('guest');

        $this->post('/complete-profile', [
            'contact_number' => '09171234567',
        ])->assertSessionHasErrors('purpose');
    }

    public function test_requires_parent_relationship_and_student_name(): void
    {
        $this->registerOther('parent');

        $this->post('/complete-profile', [
            'contact_number' => '09171234567',
        ])->assertSessionHasErrors(['relationship_to_student', 'student_full_name']);
    }

    /**
     * A student's avatar upload used to be merged into the profile update as
     * the raw UploadedFile, writing the upload's TEMP path into
     * student_profiles.avatar - which the profile page then rendered as the
     * image source. The stored path must be the only thing persisted, and the
     * photo has to render on the profile page (student_profiles first, the
     * user-record upload as the fallback).
     */
    public function test_student_avatar_upload_is_stored_and_renders_on_profile_page(): void
    {
        Storage::fake('public');

        $user = $this->registerStudent('avatar-student@example.com');

        $this->post('/complete-profile', [
            'student_id' => '2024-12345',
            'course' => 'BSIS',
            'year_level' => '2nd Year',
            'contact_number' => '09171234567',
            'address' => 'Barotac Nuevo, Iloilo',
            'avatar' => UploadedFile::fake()->image('me.jpg', 200, 200),
        ])->assertRedirect(route('dashboard', absolute: false));

        $user->refresh();

        $this->assertNotNull($user->avatar, 'the user-record upload did not persist');
        $this->assertStringStartsWith('avatars/', $user->avatar);
        Storage::disk('public')->assertExists($user->avatar);

        // A path on the profile may only ever be a stored avatar, never the
        // temp path of the request's upload.
        $profilePath = $user->studentProfile?->avatar;
        $this->assertTrue(
            $profilePath === null || str_starts_with($profilePath, 'avatars/'),
            "student_profiles.avatar holds garbage: {$profilePath}"
        );

        $this->actingAs($user)
            ->get('/student/profile')
            ->assertOk()
            ->assertSee('storage/avatars/');
    }

    /**
     * Commit f628145 removed the `$user->avatar` guard that blocked
     * first-time uploads and claims every registration type can now set a
     * photo. Prove it for alumni, guests and parents: the file lands on the
     * avatar disk, the column stores the relative path, and the dashboard
     * renders it through the sidebar.
     */
    public function test_avatar_upload_persists_for_every_other_registration_type(): void
    {
        Storage::fake('public');

        $cases = [
            'alumni' => ['year_graduated' => '2024'],
            'guest' => ['purpose' => 'inquiry'],
            'parent' => ['relationship_to_student' => 'mother', 'student_full_name' => 'Jane Student'],
        ];

        foreach ($cases as $type => $extra) {
            $user = $this->registerOther($type, "avatar-{$type}@example.com");

            $this->post('/complete-profile', $extra + [
                'contact_number' => '09171234567',
                'avatar' => UploadedFile::fake()->image('me.jpg', 200, 200),
            ])->assertRedirect(route('dashboard', absolute: false));

            $user->refresh();

            $this->assertNotNull($user->avatar, "no avatar persisted for {$type}");
            $this->assertStringStartsWith('avatars/', $user->avatar);
            Storage::disk('public')->assertExists($user->avatar);
            $this->assertStringContainsString('storage/avatars/', $user->avatar_url);

            $this->actingAs($user)
                ->get('/dashboard')
                ->assertOk()
                ->assertSee('storage/avatars/');
        }
    }
}