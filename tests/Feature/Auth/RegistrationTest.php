<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Create Your Account');
        $response->assertSee('Choose the account type that best describes you');
    }

    public function test_registration_form_with_student_type_renders(): void
    {
        $response = $this->get('/register/form?type=student');

        $response->assertStatus(200);
        $response->assertSee('Student');
    }

    public function test_registration_form_rejects_unknown_type(): void
    {
        $this->get('/register/form?type=hacker')->assertNotFound();
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'student',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('complete-profile', absolute: false));

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertDatabaseHas('student_profiles', ['user_id' => 1]);
    }

    public function test_other_type_registers_without_student_profile(): void
    {
        $response = $this->post('/register', [
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'other',
            'registration_type' => 'alumni',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('complete-profile', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'other@example.com',
            'registration_type' => 'alumni',
        ]);
        $this->assertDatabaseMissing('student_profiles', ['user_id' => 1]);
    }

    public function test_other_type_requires_registration_type(): void
    {
        $response = $this->post('/register', [
            'name' => 'Other User',
            'email' => 'other2@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'other',
        ]);

        $response->assertSessionHasErrors(['registration_type']);
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'other2@example.com']);
    }
}
