<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Who can move a booking, and what they are allowed to read to do it.
 *
 * The card sits on the registrar's list, which the registrar, a department
 * and the System Admin all reach, and the two endpoints behind it answer
 * for one office at a time. So an office account reads its own month and
 * its own slots, and gets nothing back for another office's.
 */
class RescheduleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_the_card_and_the_endpoints_behind_it(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $appointment = $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Certification',
            'date' => now()->addDays(3)->toDateString(),
            'time_slot' => '09:00 AM - 10:00 AM',
            'status' => AppointmentStatus::PENDING->value,
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/registrar/appointments')->assertOk();

        $this->actingAs($admin)
            ->getJson('/registrar/availability/month?office=Registrar&month='.now()->format('Y-m'))
            ->assertOk();

        $this->actingAs($admin)
            ->getJson('/registrar/appointments/slots?office=Registrar&date='.now()->addDays(3)->toDateString())
            ->assertOk();

        $this->actingAs($admin)
            ->putJson('/registrar/appointments/'.$appointment->id.'/reschedule', [
                'date' => now()->addDays(4)->toDateString(),
                'time_slot' => '10:00 AM - 11:00 AM',
                'reschedule_reason' => 'Office closure',
            ])
            ->assertOk();

        $this->assertEquals(AppointmentStatus::RESCHEDULED->value, $appointment->refresh()->status);
    }

    public function test_a_department_account_reads_its_own_office_only(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $appointment = $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Certification',
            'date' => now()->addDays(3)->toDateString(),
            'time_slot' => '09:00 AM - 10:00 AM',
            'status' => AppointmentStatus::PENDING->value,
        ]);

        $department = User::factory()->create([
            'role' => 'department',
            'office' => 'CICI',
        ]);

        // The card is on the shared list, so the account has to reach it.
        $this->actingAs($department)->get('/registrar/appointments')->assertOk();

        // Its own office reads fine...
        $this->actingAs($department)
            ->getJson('/registrar/availability/month?office=CICI&month='.now()->format('Y-m'))
            ->assertOk();

        // ...and another office's does not.
        $this->actingAs($department)
            ->getJson('/registrar/availability/month?office=Registrar&month='.now()->format('Y-m'))
            ->assertForbidden();

        $this->actingAs($department)
            ->getJson('/registrar/appointments/slots?office=CICI&date='.now()->addDays(3)->toDateString())
            ->assertOk();
    }
}
