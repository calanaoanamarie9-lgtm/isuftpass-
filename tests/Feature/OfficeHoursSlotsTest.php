<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Office;
use App\Models\SlotAvailability;
use App\Models\User;
use App\Support\TimeSlots;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * An office's day is not a fixed list of eight slots. The opening and closing
 * time an office keeps decide how many hourly slots it offers, and every
 * office edits its own hours on its own Availability page — the registrar's,
 * and each of the eight workspaces'.
 */
class OfficeHoursSlotsTest extends TestCase
{
    use RefreshDatabase;

    private function makeStaff(string $office, string $role): User
    {
        return User::factory()->create(['role' => $role, 'office' => $office]);
    }

    private function registrar(): User
    {
        return User::factory()->create(['role' => 'registrar']);
    }

    /**
     * AvailabilityController resolves the row before writing, so the office
     * needs one in the registry (the migration seeds six of them).
     */
    private function registerOffice(string $name): Office
    {
        return Office::firstOrCreate(
            ['name' => $name],
            ['is_active' => true, 'default_slot_capacity' => 8]
        );
    }

    public function test_a_default_day_is_every_hour_between_opening_and_closing(): void
    {
        $slots = TimeSlots::forOffice('Registrar');

        $this->assertCount(9, $slots, 'The default 8 AM to 5 PM day should offer every hour in it.');
        $this->assertSame('08:00 AM - 09:00 AM', $slots[0]);
        $this->assertContains('12:00 PM - 01:00 PM', $slots);
        $this->assertSame('04:00 PM - 05:00 PM', end($slots));
    }

    public function test_availability_page_shows_the_offices_saved_hours(): void
    {
        $this->registerOffice('OSAS');

        $this->actingAs($this->makeStaff('OSAS', 'osas'))
            ->get('/osas/availability')
            ->assertOk()
            ->assertSee('Office Hours')
            ->assertSee('value="08:00"', false)
            ->assertSee('value="17:00"', false)
            ->assertDontSee('Daily Slots')
            ->assertDontSee('slots per day');
    }

    public function test_saving_later_hours_grows_the_slot_list_everywhere(): void
    {
        $this->registerOffice('OSAS');

        $this->actingAs($this->makeStaff('OSAS', 'osas'))
            ->postJson('/osas/availability/hours', [
                'open_time' => '08:00',
                'close_time' => '19:00',
            ])
            ->assertOk()
            ->assertJsonPath('count', 11);

        $slots = TimeSlots::forOffice('OSAS');

        $this->assertCount(11, $slots);
        $this->assertSame('06:00 PM - 07:00 PM', end($slots));

        // The booking form students see reads the same office hours.
        $date = now()->addDays(2)->toDateString();

        $offered = collect(
            $this->actingAs(User::factory()->create(['role' => 'student']))
                ->getJson('/student/appointments/slots?office=OSAS&date=' . $date)
                ->json()
        )->pluck('time')->all();

        $this->assertSame($slots, $offered, 'Students must be offered the office\'s own slot list.');
    }

    public function test_hours_only_change_the_office_that_saved_them(): void
    {
        $this->registerOffice('OSAS');

        $this->actingAs($this->makeStaff('OSAS', 'osas'))
            ->postJson('/osas/availability/hours', [
                'open_time' => '08:00',
                'close_time' => '19:00',
            ])
            ->assertOk();

        $this->assertCount(11, TimeSlots::forOffice('OSAS'));
        $this->assertCount(count(Appointment::TIME_SLOTS), TimeSlots::forOffice('Registrar'));
        $this->assertSame('08:00', Office::where('name', 'OSAS')->first()->open_time);
    }

    public function test_the_registrar_edits_its_hours_from_its_own_page(): void
    {
        $this->actingAs($this->registrar())
            ->postJson('/registrar/availability/hours', [
                'open_time' => '07:00',
                'close_time' => '20:00',
            ])
            ->assertOk()
            ->assertJsonPath('count', 13);

        $this->assertCount(13, TimeSlots::forOffice('Registrar'));

        $this->actingAs($this->registrar())
            ->get('/registrar/availability')
            ->assertOk()
            ->assertSee('Office Hours')
            ->assertDontSee('Daily Slots')
            ->assertDontSee('slots per day')
            ->assertSee('07:00 PM - 08:00 PM');
    }

    public function test_closing_time_must_follow_the_opening_time(): void
    {
        $office = $this->registerOffice('OSAS');

        $this->actingAs($this->makeStaff('OSAS', 'osas'))
            ->postJson('/osas/availability/hours', [
                'open_time' => '17:00',
                'close_time' => '08:00',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('close_time');

        $this->assertSame('08:00', $office->fresh()->open_time, 'A rejected save must leave the hours alone.');
    }

    public function test_a_slot_outside_office_hours_cannot_be_booked(): void
    {
        $this->registerOffice('OSAS');

        $this->actingAs(User::factory()->create(['role' => 'student']))
            ->post('/student/appointments', [
                'office' => 'OSAS',
                'purpose' => 'Enrollment clearance',
                'date' => now()->addDays(2)->toDateString(),
                'time_slot' => '07:00 PM - 08:00 PM',
            ])
            ->assertSessionHasErrors('time_slot');

        $this->assertSame(0, Appointment::count());
    }

    public function test_shrinking_hours_drops_slot_rules_outside_the_new_day(): void
    {
        $this->registerOffice('OSAS');
        $date = now()->addDays(3)->toDateString();

        $staff = $this->makeStaff('OSAS', 'osas');

        $this->actingAs($staff)
            ->postJson('/osas/availability/save', ['date' => $date, 'type' => 'open'])
            ->assertOk();

        $this->assertCount(9, SlotAvailability::whereDate('date', $date)->get());

        $this->actingAs($staff)
            ->postJson('/osas/availability/hours', [
                'open_time' => '08:00',
                'close_time' => '12:00',
            ])
            ->assertOk()
            ->assertJsonPath('count', 4);

        $this->actingAs($staff)
            ->postJson('/osas/availability/save', ['date' => $date, 'type' => 'open'])
            ->assertOk();

        $rules = SlotAvailability::whereDate('date', $date)->get();

        $this->assertCount(4, $rules, 'Afternoon rules belong to a day the office no longer keeps.');
        $this->assertTrue($rules->every(fn (SlotAvailability $rule) => $rule->status === 'available'));
    }

    public function test_office_can_update_its_slot_capacity(): void
    {
        $office = $this->registerOffice('OSAS');

        $staff = $this->makeStaff('OSAS', 'osas');

        $this->actingAs($staff)
            ->postJson('/osas/availability/hours', [
                'open_time' => '08:00',
                'close_time' => '17:00',
                'default_slot_capacity' => 15,
            ])
            ->assertOk()
            ->assertJsonPath('default_slot_capacity', 15);

        $this->assertSame(15, $office->fresh()->default_slot_capacity);
    }

    public function test_availability_can_be_saved_with_custom_capacity_and_open_entire_day(): void
    {
        $office = $this->registerOffice('CICI');
        $date = now()->addDays(2)->toDateString();
        $staff = $this->makeStaff('CICI', 'department');

        $this->actingAs($staff)
            ->postJson('/cici/availability/save', [
                'date' => $date,
                'type' => 'open',
                'max_capacity' => 12,
            ])
            ->assertOk();

        $rules = SlotAvailability::where('office_id', $office->id)->whereDate('date', $date)->get();
        $this->assertNotEmpty($rules);
        $this->assertTrue($rules->every(fn ($r) => $r->status === 'available' && $r->max_capacity === 12));
    }
}
