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
        $response->assertSee('Profile Information');
    }

    public function test_profile_page_renders_for_staff(): void
    {
        $user = User::factory()->create(['role' => 'registrar']);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $response->assertSee('Profile Information');
    }

    public function test_appointment_booking_page_renders_slot_dropdown(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->actingAs($user)
            ->get('/student/appointments/create')
            ->assertOk()
            ->assertSee('Available Time Slots')
            ->assertSee('Select an office and date first');
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