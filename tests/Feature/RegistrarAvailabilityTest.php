<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\SlotAvailability;
use App\Models\User;
use App\Support\SlotAvailabilityService;
use App\Support\TimeSlots;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrarAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private function makeRegistrar(): User
    {
        return User::factory()->create(['role' => 'registrar']);
    }

    private function futureDate(int $days = 3): string
    {
        return now()->addDays($days)->toDateString();
    }

    public function test_registrar_can_view_availability_page(): void
    {
        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/availability')
            ->assertOk()
            ->assertSee('Availability Schedule')
            ->assertSee('Set Availability')
            ->assertSee(Appointment::TIME_SLOTS[0]);
    }

    public function test_the_slot_list_numbers_each_slot_instead_of_printing_its_time(): void
    {
        $slots = TimeSlots::forOffice('Registrar');

        $html = $this->actingAs($this->makeRegistrar())
            ->get('/registrar/availability')
            ->assertOk()
            ->getContent();

        // Each row is a checkbox followed straight away by its number, with
        // nothing but whitespace in between — which is what proves the time
        // itself is no longer printed in the list.
        preg_match_all(
            '/type="checkbox"\s+value="([^"]+)"[^>]*>\s*<span[^>]*>\s*(\d+)\s*<\/span>/',
            $html,
            $pairs,
            PREG_SET_ORDER
        );

        $this->assertCount(
            count($slots),
            $pairs,
            'Expected every time slot to appear in the list.'
        );

        foreach ($pairs as $index => $pair) {
            // The value still carries the real time, so saving is unchanged.
            $this->assertSame($slots[$index], $pair[1]);

            // The label shows the slot's position instead.
            $this->assertSame((string) ($index + 1), $pair[2]);
        }
    }

    public function test_student_cannot_access_availability_management(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get('/registrar/availability')
            ->assertForbidden();
    }

    public function test_save_open_day_makes_all_slots_available(): void
    {
        $date = $this->futureDate();

        $this->actingAs($this->makeRegistrar())
            ->postJson('/registrar/availability/save', [
                'date' => $date,
                'type' => 'open',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Registrar availability has been updated.');

        $rules = SlotAvailability::whereDate('date', $date)->get();

        $this->assertCount(count(Appointment::TIME_SLOTS), $rules);
        $this->assertTrue($rules->every(fn (SlotAvailability $rule) => $rule->status === 'available'));

        $slots = Appointment::availableSlots('Registrar', $date);
        $this->assertTrue(collect($slots)->every(fn (array $slot) => $slot['is_open']));
    }

    public function test_save_closed_day_blocks_all_slots(): void
    {
        $date = $this->futureDate();

        $this->actingAs($this->makeRegistrar())
            ->postJson('/registrar/availability/save', [
                'date' => $date,
                'type' => 'closed',
            ])
            ->assertOk();

        $slots = Appointment::availableSlots('Registrar', $date);
        $this->assertTrue(collect($slots)->every(fn (array $slot) => ! $slot['is_open']));
    }

    public function test_save_specific_slots_persists_selection(): void
    {
        $date = $this->futureDate();
        $chosen = [Appointment::TIME_SLOTS[0], Appointment::TIME_SLOTS[2]];

        $this->actingAs($this->makeRegistrar())
            ->postJson('/registrar/availability/save', [
                'date' => $date,
                'type' => 'slots',
                'slots' => $chosen,
            ])
            ->assertOk();

        foreach ($chosen as $slot) {
            $this->assertSame(
                'available',
                SlotAvailability::whereDate('date', $date)->where('time_slot', $slot)->value('status')
            );
        }

        $blocked = collect(Appointment::TIME_SLOTS)->reject(fn (string $slot) => in_array($slot, $chosen, true));

        foreach ($blocked as $slot) {
            $this->assertSame(
                'blocked',
                SlotAvailability::whereDate('date', $date)->where('time_slot', $slot)->value('status')
            );
        }
    }

    public function test_save_rejects_past_dates(): void
    {
        $past = now()->subDay()->toDateString();

        $this->actingAs($this->makeRegistrar())
            ->postJson('/registrar/availability/save', [
                'date' => $past,
                'type' => 'open',
            ])
            ->assertJsonValidationErrors('date');
    }

    public function test_settings_returns_current_configuration(): void
    {
        $date = $this->futureDate();
        $service = app(SlotAvailabilityService::class);
        $officeId = $service->officeIdFor('Registrar');

        foreach (Appointment::TIME_SLOTS as $index => $slot) {
            SlotAvailability::create([
                'office_id' => $officeId,
                'date' => $date,
                'time_slot' => $slot,
                'max_capacity' => 5,
                'status' => $index === 0 ? 'available' : 'blocked',
            ]);
        }

        $this->actingAs($this->makeRegistrar())
            ->getJson('/registrar/availability/settings/'.$date)
            ->assertOk()
            ->assertJsonPath('type', 'slots')
            ->assertJsonPath('slots.0', Appointment::TIME_SLOTS[0]);
    }

    public function test_schedule_lists_configured_dates(): void
    {
        $date = $this->futureDate();
        $service = app(SlotAvailabilityService::class);
        $officeId = $service->officeIdFor('Registrar');

        foreach (Appointment::TIME_SLOTS as $slot) {
            SlotAvailability::create([
                'office_id' => $officeId,
                'date' => $date,
                'time_slot' => $slot,
                'max_capacity' => 5,
                'status' => 'blocked',
            ]);
        }

        $this->actingAs($this->makeRegistrar())
            ->getJson('/registrar/availability/schedule')
            ->assertOk()
            ->assertJsonPath('schedule.0.id', $date)
            ->assertJsonPath('schedule.0.type', 'closed');
    }
}
