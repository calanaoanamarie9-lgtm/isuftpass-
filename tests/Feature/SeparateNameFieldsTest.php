<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The applicant's name is captured as three separate, required parts (last,
 * first, middle). They are stored independently, kept in sync with the display
 * `name` the rest of the app renders, and formatted as
 * "[Last Name], [First Name] [Middle Name]" on official print-outs.
 */
class SeparateNameFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_stores_the_three_name_parts_and_composes_the_display_name(): void
    {
        $this->post('/register', [
            'first_name' => 'Juan',
            'middle_name' => 'Miguel',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'student',
        ]);

        $user = User::query()->where('email', 'juan@example.com')->firstOrFail();

        $this->assertSame('Dela Cruz', $user->last_name);
        $this->assertSame('Juan', $user->first_name);
        $this->assertSame('Miguel', $user->middle_name);

        // The display name the rest of the app renders stays a natural order.
        $this->assertSame('Juan Miguel Dela Cruz', $user->name);

        // Official print-outs use the "[Last], [First] [Middle]" format.
        $this->assertSame('Dela Cruz, Juan Miguel', $user->formattedName());
    }

    public function test_registration_requires_each_name_part(): void
    {
        $this->post('/register', [
            'email' => 'missing@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'user_type' => 'student',
        ])->assertSessionHasErrors(['last_name', 'first_name', 'middle_name']);

        $this->assertDatabaseMissing('users', ['email' => 'missing@example.com']);
    }

    public function test_formatted_name_renders_last_first_middle(): void
    {
        $user = User::factory()->create([
            'last_name' => 'Santos',
            'first_name' => 'Ana',
            'middle_name' => 'Reyes',
        ]);

        $this->assertSame('Santos, Ana Reyes', $user->formattedName());
    }

    public function test_formatted_name_falls_back_to_the_display_name_for_legacy_users(): void
    {
        $user = User::factory()->create([
            'name' => 'Pedro Penduko',
            'last_name' => null,
            'first_name' => null,
            'middle_name' => null,
        ]);

        $this->assertSame('Pedro Penduko', $user->formattedName());
    }

    public function test_profile_settings_saves_the_name_parts_and_composes_the_display_name(): void
    {
        $user = User::factory()->create(['role' => 'student', 'name' => 'Old Name']);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'last_name' => 'Cruz',
                'first_name' => 'Juan',
                'middle_name' => 'Santos',
                'email' => $user->email,
            ])
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('Cruz', $user->last_name);
        $this->assertSame('Juan', $user->first_name);
        $this->assertSame('Santos', $user->middle_name);
        $this->assertSame('Juan Santos Cruz', $user->name);
        $this->assertSame('Cruz, Juan Santos', $user->formattedName());
    }
}
