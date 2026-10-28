<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CompleteProfileTest extends TestCase
{
    use RefreshDatabase;

    private function registerStudent(string $email = 'jane@example.com'): User
    {
        $this->post('/register', [
            'first_name' => 'Jane',
            'middle_name' => 'M',
            'last_name' => 'Student',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'student',
        ]);

        return $this->verified(auth()->user());
    }

    private function registerOther(string $registrationType, string $email = 'john@example.com'): User
    {
        // Parents and guests give one Full Name; alumni keep the three parts.
        $name = in_array($registrationType, ['parent', 'guest'], true)
            ? ['full_name' => 'John Other']
            : ['first_name' => 'John', 'middle_name' => 'M', 'last_name' => 'Other'];

        $this->post('/register', $name + [
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

    /**
     * The complete-details form carries no photo field: profile pictures
     * live in Profile Settings, so onboarding must neither offer an avatar
     * input nor declare multipart.
     */
    public function test_the_form_no_longer_offers_a_profile_picture(): void
    {
        $this->registerStudent();

        $this->get('/complete-profile')
            ->assertOk()
            ->assertDontSee('name="avatar"', false)
            ->assertDontSee('Profile Picture')
            ->assertDontSee('enctype="multipart/form-data"', false);
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

    /**
     * Address, course and year level are required for students - the school
     * needs them to know who it is dealing with. Only the student form shows
     * these three fields, so only the student form may demand them: the
     * alumni, guest and parent forms never submit them, and a blanket
     * `required` would bounce every one of those saves.
     */
    public function test_student_requires_address_course_and_year_level(): void
    {
        $this->registerStudent();

        $this->post('/complete-profile', [
            'contact_number' => '09171234567',
            'student_id' => '2024-12345',
        ])->assertSessionHasErrors(['address', 'course', 'year_level']);
    }

    /**
     * The three fields must be marked required in the markup too, so the
     * browser stops the student before the round trip instead of after it,
     * and the labels may no longer promise a field is optional.
     */
    public function test_the_student_form_marks_address_course_and_year_level_required(): void
    {
        $this->registerStudent();

        $html = $this->get('/complete-profile')->assertOk()->getContent();

        foreach (['address', 'course', 'year_level'] as $field) {
            $this->assertMatchesRegularExpression(
                '/name="'.$field.'"[^>]*\srequired\b[^>]*>/s',
                $html,
                "the student form's {$field} field must carry required"
            );
        }

        foreach (['Address', 'Course', 'Year Level'] as $label) {
            $this->assertStringNotContainsString(
                $label.' <span class="text-gray-400 font-normal">(optional)</span>',
                $html,
                "{$label} must no longer be labelled optional"
            );
        }
    }

    /**
     * The other three registration types must keep their own forms, which
     * submit none of these fields - proof that scoping the rule to students
     * did not accidentally make them required for everyone.
     */
    public function test_address_course_and_year_level_stay_optional_for_other_types(): void
    {
        foreach (['alumni' => ['year_graduated' => '2024'], 'guest' => ['purpose' => 'inquiry'], 'parent' => ['relationship_to_student' => 'mother', 'student_full_name' => 'Jane Student']] as $type => $extra) {
            // /register sits behind `guest`: signing out first is what makes
            // each pass of the loop register the type it names.
            $this->post('/logout');

            $this->registerOther($type, "optional-{$type}@example.com");

            $this->post('/complete-profile', $extra + [
                'contact_number' => '09171234567',
            ])
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('dashboard', absolute: false));
        }
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
     * A photo posted straight at this endpoint (the old form's field, a
     * stale tab) is ignored: profile pictures are managed in Profile
     * Settings now, never accepted by complete-details.
     */
    public function test_student_avatar_post_is_ignored_on_complete_details(): void
    {
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

        $this->assertNull($user->avatar_data, 'complete-details must not store a photo');
        $this->assertNull($user->avatar);
    }

    /**
     * The same applies to alumni, guests and parents: complete-details
     * accepts no photo for any registration type.
     */
    public function test_avatar_posts_are_ignored_for_every_other_registration_type(): void
    {
        $cases = [
            'alumni' => ['year_graduated' => '2024'],
            'guest' => ['purpose' => 'inquiry'],
            'parent' => ['relationship_to_student' => 'mother', 'student_full_name' => 'Jane Student'],
        ];

        foreach ($cases as $type => $extra) {
            // /register is guest-only: the previous iteration's session would
            // keep the earlier user, so every later iteration would silently
            // re-test that same account instead of the type under test.
            if (auth()->check()) {
                $this->post('/logout');
            }

            $user = $this->registerOther($type, "avatar-{$type}@example.com");

            $this->post('/complete-profile', $extra + [
                'contact_number' => '09171234567',
                'avatar' => UploadedFile::fake()->image('me.jpg', 200, 200),
            ])->assertRedirect(route('dashboard', absolute: false));

            $user->refresh();

            $this->assertSame($type, $user->registration_type);
            $this->assertNull($user->avatar_data, "complete-details stored a photo for {$type}");
        }
    }
}
