<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\SlotAvailability;
use App\Models\User;
use App\Support\SlotAvailabilityService;
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

    /**
     * Book a student into the Registrar on a given date, under a name of our
     * choosing when the test cares who is on the list and in what order.
     */
    private function bookRegistrar(
        string $date,
        string $status = 'pending',
        ?string $name = null,
    ): void {
        User::factory()->create(array_filter([
            'role' => 'student',
            'name' => $name,
        ]))
            ->appointments()
            ->create([
                'office' => 'Registrar',
                'purpose' => 'Certification',
                'date' => $date,
                'time_slot' => '09:00 AM - 10:00 AM',
                'status' => $status,
            ]);
    }

    /**
     * Pull the day's rosters back out of the page. They arrive as JSON inside
     * the Alpine component rather than as rendered rows, so the names have to
     * be decoded before anything can be said about them.
     *
     * @return array<string, list<string>>
     */
    private function rosterFrom(string $html): array
    {
        $this->assertMatchesRegularExpression(
            '/rosterByDate:\s*\{/',
            $html,
            'The page must be handed the day rosters.'
        );

        preg_match('/rosterByDate:\s*(\{.*?\}),/s', $html, $matches);

        $this->assertNotEmpty($matches[1] ?? null, 'Could not find the roster payload.');

        $roster = json_decode($matches[1], true);

        $this->assertIsArray($roster, 'The roster payload must be valid JSON.');

        return $roster;
    }

    public function test_the_list_numbers_the_days_appointments_rather_than_the_offices_times(): void
    {
        $date = $this->futureDate();

        // Twelve people on one day. The list has to run 1..12 — past the ten
        // that fill a day — because the registrar is reading how full the day
        // is, not picking between clock times.
        foreach (range(1, 12) as $ignored) {
            $this->bookRegistrar($date);
        }

        $html = $this->actingAs($this->makeRegistrar())
            ->get('/registrar/availability')
            ->assertOk()
            ->getContent();

        // The page is handed the day's people and draws one row per person,
        // numbered by arrival position rather than printed with a clock time.
        $roster = $this->rosterFrom($html);

        $this->assertCount(
            12,
            $roster[$date] ?? [],
            'Expected the date to carry its people to the page.'
        );
        $this->assertStringContainsString('x-for="(name, i) in roster"', $html);
        $this->assertStringContainsString('x-text="i + 1"', $html);

        // Beyond the first ten the row says so, since booking is unlimited.
        $this->assertStringContainsString('Bukas na', $html);

        // The hourly checkbox list this replaces is gone.
        $this->assertDoesNotMatchRegularExpression(
            '/type="checkbox"\s+value="[^"]+"\s+x-model="selectedSlots"/',
            $html
        );
    }

    public function test_a_cancelled_booking_is_left_off_the_roster_and_the_rest_keep_arrival_order(): void
    {
        $date = $this->futureDate();

        $this->bookRegistrar($date, 'pending', 'Ada Arrived First');
        $this->bookRegistrar($date, 'cancelled', 'Cleo Walked Away');
        $this->bookRegistrar($date, 'pending', 'Bea Arrived Second');

        $html = $this->actingAs($this->makeRegistrar())
            ->get('/registrar/availability')
            ->assertOk()
            ->getContent();

        $roster = $this->rosterFrom($html);

        $this->assertSame(
            ['Ada Arrived First', 'Bea Arrived Second'],
            $roster[$date] ?? null,
            'A booking nobody will occupy is left off, and the rest keep the order they arrived in.'
        );
    }

    public function test_a_day_with_no_bookings_says_so_instead_of_listing_rows(): void
    {
        $html = $this->actingAs($this->makeRegistrar())
            ->get('/registrar/availability')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('No appointments on this date yet.', $html);
        $this->assertStringContainsString('Booking is unlimited', $html);
    }

    public function test_the_days_size_is_its_own_number_not_the_offices_hours(): void
    {
        $html = $this->actingAs($this->makeRegistrar())
            ->get('/registrar/availability')
            ->assertOk()
            ->getContent();

        // The registrar's day runs 08:00-16:00, so counting clock hours
        // would report eight. The day's size is handed over as its own
        // number instead, and both the list and the preview read off it.
        $this->assertStringContainsString(
            'slotsPerDay: '.Appointment::SLOTS_PER_DAY,
            $html,
            'The day must be sized by its slots, not by the hours the office keeps.'
        );
        $this->assertStringContainsString('this.slotsPerDay - this.rosterCount', $html);
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

    public function test_calendar_management_entry_shows_only_the_date_and_what_changed(): void
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
            ->get('/registrar/availability')
            ->assertOk()
            // The entry is the date plus the badge naming what was changed...
            ->assertSee('x-text="item.date"', false)
            ->assertSee('x-text="item.typeLabel"', false)
            // ...and nothing about the individual times behind it.
            ->assertDontSee('x-text="item.status"', false)
            ->assertDontSee('No appointments can be booked on this date.')
            ->assertDontSee('All official time slots are available.')
            ->assertDontSee('slotNumber', false);
    }
}
