<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The header bell was removed from the layout's top bars: roles reach
 * alerts through the sidebar's Notifications entry instead. These pins
 * keep the bell from creeping back into any role's header.
 */
class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_bell_is_gone_from_the_student_header(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get(route('student.appointments.index'))
            ->assertOk()
            ->assertDontSee('bellOpen')
            ->assertDontSee('No notifications yet.');
    }

    public function test_the_bell_is_gone_from_staff_headers(): void
    {
        $registrar = User::factory()->create(['role' => 'registrar']);

        $this->actingAs($registrar)
            ->get(route('registrar.document-requests.index'))
            ->assertOk()
            ->assertDontSee('bellOpen');

        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)
            ->get(route('cashier.payments.pending'))
            ->assertOk()
            ->assertDontSee('bellOpen');
    }
}
