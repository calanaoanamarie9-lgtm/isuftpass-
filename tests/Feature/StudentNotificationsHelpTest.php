<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentBookedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentNotificationsHelpTest extends TestCase
{
    use RefreshDatabase;

    protected function createAppointmentFor(User $user): Appointment
    {
        return $user->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Certification',
            'date' => now()->addDays(2)->toDateString(),
            'time_slot' => '09:00 - 10:00 AM',
            'status' => 'pending',
        ]);
    }

    public function test_notifications_page_renders_with_empty_state(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->actingAs($user)
            ->get('/student/notifications')
            ->assertOk()
            ->assertSee('No notifications yet');
    }

    public function test_notifications_page_lists_and_marks_read(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $user->notify(new AppointmentBookedNotification($this->createAppointmentFor($user)));

        $this->actingAs($user)
            ->get('/student/notifications')
            ->assertOk()
            ->assertSee('Appointment Booked')
            ->assertSeeText('1');

        $this->actingAs($user)
            ->put('/student/notifications/read-all')
            ->assertRedirect();

        $this->assertNotNull($user->notifications()->first()->read_at);
    }

    public function test_open_marks_read_and_redirects(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $user->notify(new AppointmentBookedNotification($this->createAppointmentFor($user)));
        $notification = $user->notifications()->first();

        $this->actingAs($user)
            ->get('/student/notifications/' . $notification->id . '/open')
            ->assertRedirect(route('student.appointments.index'));

        $this->assertNotNull($notification->refresh()->read_at);
    }

    public function test_help_page_renders_with_office_directory(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        $this->actingAs($user)
            ->get('/student/help')
            ->assertOk()
            ->assertSee('Office Directory')
            ->assertSee("Registrar's Office")
            ->assertSee('How do I book an appointment?');
    }
}