<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders_for_student(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $response->assertSee('Account Information');
    }

    public function test_profile_page_renders_for_staff(): void
    {
        $user = User::factory()->create(['role' => 'registrar']);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $response->assertSee('Account Information');
    }

    public function test_appointment_booking_page_asks_for_a_day_not_an_hour(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->actingAs($user)
            ->get('/student/appointments/create')
            ->assertOk()
            // The student picks a day; the office answers with the hour.
            ->assertSee('Preferred Date')
            ->assertDontSee('Available Time Slots')
            ->assertDontSee('Select an available time slot');
    }

    public function test_my_appointments_page_renders(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->actingAs($user)
            ->get('/student/appointments')
            ->assertOk()
            ->assertSee('My Appointments');
    }
}
