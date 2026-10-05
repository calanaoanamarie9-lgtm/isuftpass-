<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteProfileTest extends TestCase
{
    use RefreshDatabase;

    private function registerStudent(): User
    {
        $this->post('/register', [
            'name' => 'Jane Student',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'student',
        ]);

        return $this->verified(auth()->user());
    }

    private function registerOther(string $registrationType): User
    {
        $this->post('/register', [
            'name' => 'John Other',
            'email' => 'john@example.com',
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
}