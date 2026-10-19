<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Office;
use App\Models\SlotAvailability;
use App\Models\User;
use App\Support\SlotAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SlotAvailabilityCapacityTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeStudent(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    private function registrarOffice(): Office
    {
        return Office::where('name', 'Registrar')->firstOrFail();
    }

    private function registerSlot(string $timeSlot = '09:00 AM - 10:00 AM'): void
    {
        $this->actingAs($this->makeAdmin())->post(route('admin.slots.store'), [
            'office_id' => $this->registrarOffice()->id,
            'date' => now()->addDays(3)->toDateString(),
            'time_slot' => $timeSlot,
            'max_capacity' => 2,
            'status' => SlotAvailability::STATUS_AVAILABLE,
        ])->assertRedirect()->assertSessionHas('status');
    }

    public function test_admin_can_view_slot_capacity_page(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(route('admin.slots.index'))
            ->assertOk()
            ->assertSee('Slot Availability')
            ->assertSee('Registrar')
            ->assertSee('08:00 AM - 09:00 AM');
    }

    public function test_office_default_capacity_is_used_without_rules(): void
    {
        $service = app(SlotAvailabilityService::class);

        $check = $service->checkForOffice('Registrar', now()->addDays(3)->toDateString(), '09:00 AM - 10:00 AM');

        $this->assertTrue($check['available']);
        $this->assertSame(6, $check['limit']);
        $this->assertSame(0, $check['booked']);
        $this->assertEquals(Appointment::SLOT_LIMITS['Registrar'], $check['limit']);
    }

    public function test_admin_can_set_date_specific_slot_rule(): void
    {
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($this->makeAdmin())->post(route('admin.slots.store'), [
            'office_id' => $this->registrarOffice()->id,
            'date' => $date,
            'time_slot' => '09:00 AM - 10:00 AM',
            'max_capacity' => 2,
            'status' => SlotAvailability::STATUS_AVAILABLE,
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseHas('slot_availabilities', [
            'office_id' => $this->registrarOffice()->id,
            'date' => $date,
            'time_slot' => '09:00 AM - 10:00 AM',
            'max_capacity' => 2,
        ]);

        $this->assertDatabaseHas('audit_logs', ['action' => 'slot_capacity.saved']);

        $check = app(SlotAvailabilityService::class)
            ->checkForOffice('Registrar', $date, '09:00 AM - 10:00 AM');

        $this->assertSame(2, $check['limit']);
        $this->assertSame(2, $check['remaining']);
    }

    public function test_default_rule_applies_to_all_dates(): void
    {
        $this->actingAs($this->makeAdmin())->post(route('admin.slots.store'), [
            'office_id' => $this->registrarOffice()->id,
            'date' => now()->addDays(3)->toDateString(),
            'time_slot' => '01:00 PM - 02:00 PM',
            'max_capacity' => 3,
            'status' => SlotAvailability::STATUS_AVAILABLE,
            'apply_to_all_dates' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('slot_availabilities', [
            'office_id' => $this->registrarOffice()->id,
            'date' => null,
            'time_slot' => '01:00 PM - 02:00 PM',
            'max_capacity' => 3,
        ]);

        $check = app(SlotAvailabilityService::class)
            ->checkForOffice('Registrar', now()->addDays(9)->toDateString(), '01:00 PM - 02:00 PM');

        $this->assertSame(3, $check['limit']);
    }

    public function test_blocked_slot_is_unavailable_and_rejects_booking(): void
    {
        $date = now()->addDays(3)->toDateString();
        $slot = '10:00 AM - 11:00 AM';

        $this->actingAs($this->makeAdmin())->post(route('admin.slots.store'), [
            'office_id' => $this->registrarOffice()->id,
            'date' => $date,
            'time_slot' => $slot,
            'max_capacity' => 5,
            'status' => SlotAvailability::STATUS_BLOCKED,
        ])->assertRedirect();

        $check = app(SlotAvailabilityService::class)->checkForOffice('Registrar', $date, $slot);

        $this->assertFalse($check['available']);
        $this->assertSame(0, $check['remaining']);
        $this->assertSame(SlotAvailability::STATUS_BLOCKED, $check['status']);

        Mail::fake();
        Notification::fake();

        $this->actingAs($this->makeStudent())
            ->post(route('student.appointments.store'), [
                'office' => 'Registrar',
                'purpose' => 'Enrollment',
                'date' => $date,
                'time_slot' => $slot,
                'notes' => '',
            ])
            ->assertStatus(422);

        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_slot_marks_fully_booked_when_capacity_reached(): void
    {
        $date = now()->addDays(3)->toDateString();
        $slot = '11:00 AM - 12:00 PM';

        $this->registerSlot($slot);

        $student = $this->makeStudent();
        $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Enrollment',
            'date' => $date,
            'time_slot' => $slot,
            'status' => AppointmentStatus::CONFIRMED->value,
        ]);

        $check = app(SlotAvailabilityService::class)->checkForOffice('Registrar', $date, $slot);

        $this->assertSame(1, $check['booked']);
        $this->assertSame(1, $check['remaining']);
        $this->assertTrue($check['available']);

        $other = $this->makeStudent();
        $other->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Enrollment',
            'date' => $date,
            'time_slot' => $slot,
            'status' => AppointmentStatus::CONFIRMED->value,
        ]);

        $check = app(SlotAvailabilityService::class)->checkForOffice('Registrar', $date, $slot);

        $this->assertSame(SlotAvailability::STATUS_FULLY_BOOKED, $check['status']);
        $this->assertFalse($check['available']);
        $this->assertSame(0, $check['remaining']);

        $this->assertDatabaseHas('slot_availabilities', [
            'office_id' => $this->registrarOffice()->id,
            'date' => $date,
            'time_slot' => $slot,
            'booked_slots' => 2,
            'status' => SlotAvailability::STATUS_FULLY_BOOKED,
        ]);

        Mail::fake();
        Notification::fake();

        $this->actingAs($this->makeStudent())
            ->post(route('student.appointments.store'), [
                'office' => 'Registrar',
                'purpose' => 'Enrollment',
                'date' => $date,
                'time_slot' => $slot,
                'notes' => '',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('appointments', [
            'office' => 'Registrar',
            'time_slot' => $slot,
            'status' => AppointmentStatus::FOR_RESCHEDULE->value,
            'reschedule_reason' => 'Schedule capacity has been reached.',
        ]);
    }

    public function test_reschedule_includes_existing_booking_in_count(): void
    {
        $date = now()->addDays(3)->toDateString();
        $slot = '02:00 PM - 03:00 PM';

        $this->registerSlot($slot);

        $student = $this->makeStudent();
        $booking = $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Enrollment',
            'date' => $date,
            'time_slot' => $slot,
            'status' => AppointmentStatus::PENDING->value,
        ]);

        $other = $this->makeStudent();
        $other->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Enrollment',
            'date' => $date,
            'time_slot' => $slot,
            'status' => AppointmentStatus::CONFIRMED->value,
        ]);

        $forBooker = app(SlotAvailabilityService::class)->checkForOffice('Registrar', $date, $slot, $booking->id);
        $this->assertSame(1, $forBooker['remaining']);

        $forNew = app(SlotAvailabilityService::class)->checkForOffice('Registrar', $date, $slot);
        $this->assertSame(0, $forNew['remaining']);
    }

    public function test_admin_can_update_office_default_capacity(): void
    {
        $office = $this->registrarOffice();

        $this->actingAs($this->makeAdmin())
            ->put(route('admin.slots.capacity', $office), [
                'default_slot_capacity' => 3,
            ])
            ->assertRedirect()->assertSessionHas('status');

        $this->assertSame(3, $office->fresh()->default_slot_capacity);
        $this->assertDatabaseHas('audit_logs', ['action' => 'slot_capacity.default']);

        $check = app(SlotAvailabilityService::class)
            ->checkForOffice('Registrar', now()->addDays(3)->toDateString(), '03:00 PM - 04:00 PM');

        $this->assertSame(3, $check['limit']);
    }

    public function test_admin_can_reset_date_specific_override(): void
    {
        $date = now()->addDays(3)->toDateString();
        $slot = '04:00 PM - 05:00 PM';

        $this->registerSlot($slot);

        $rule = SlotAvailability::where('time_slot', $slot)->whereDate('date', $date)->firstOrFail();

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.slots.destroy', $rule))
            ->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseMissing('slot_availabilities', ['id' => $rule->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'slot_capacity.deleted']);

        $check = app(SlotAvailabilityService::class)->checkForOffice('Registrar', $date, $slot);

        $this->assertSame(Appointment::SLOT_LIMITS['Registrar'], $check['limit']);
    }

    public function test_student_slot_json_uses_rules(): void
    {
        $date = now()->addDays(3)->toDateString();
        $this->registerSlot();

        $student = $this->makeStudent();

        $this->actingAs($student)
            ->get(route('student.appointments.slots') . '?office=Registrar&date=' . $date)
            ->assertOk()
            ->assertJsonFragment(['time' => '09:00 AM - 10:00 AM', 'limit' => 2, 'is_open' => true]);
    }

    public function test_student_month_availability_endpoint_colors_days(): void
    {
        $today = now()->startOfDay();

        $future = $today->copy()->addDays(3);
        if ($future->gt($today->copy()->endOfMonth())) {
            $future = $today->copy()->endOfMonth();
        }

        $month = $future->format('Y-m');

        $this->registerSlot();

        $body = $this->actingAs($this->makeStudent())
            ->get(route('student.appointments.availability') . '?office=Registrar&month=' . $month)
            ->assertOk()
            ->json();

        $date = $future->toDateString();

        if (! $body[$date]['available']) {
            $this->fail('DEBUG ' . json_encode([
                'date' => $date,
                'day' => $body[$date] ?? null,
                'rules' => SlotAvailability::all(['office_id', 'date', 'time_slot', 'max_capacity', 'status'])->toArray(),
            ]));
        }

        $this->assertFalse($body[$date]['past']);
        $this->assertTrue($body[$date]['available']);
        $this->assertSame('open', $body[$date]['status']);

        $past = $today->copy()->subDay();
        if (substr($past->toDateString(), 0, 7) === $month) {
            $this->assertTrue($body[$past->toDateString()]['past']);
        }
    }

    public function test_month_availability_marks_day_red_when_fully_booked(): void
    {
        $date = now()->addDays(3)->toDateString();
        $month = substr($date, 0, 7);

        $admin = $this->makeAdmin();

        foreach (Appointment::TIME_SLOTS as $slot) {
            $this->actingAs($admin)->post(route('admin.slots.store'), [
                'office_id' => $this->registrarOffice()->id,
                'date' => $date,
                'time_slot' => $slot,
                'max_capacity' => 1,
                'status' => SlotAvailability::STATUS_AVAILABLE,
            ])->assertRedirect();

            $this->makeStudent()->appointments()->create([
                'office' => 'Registrar',
                'purpose' => 'Enrollment',
                'date' => $date,
                'time_slot' => $slot,
                'status' => AppointmentStatus::CONFIRMED->value,
            ]);
        }

        $body = $this->actingAs($this->makeStudent())
            ->get(route('student.appointments.availability') . '?office=Registrar&month=' . $month)
            ->assertOk()
            ->json();

        $this->assertFalse($body[$date]['past']);
        $this->assertFalse($body[$date]['available']);
        $this->assertSame(0, $body[$date]['open']);
        $this->assertSame('full', $body[$date]['status']);
    }

    public function test_registrar_month_endpoint_supports_office_param(): void
    {
        $month = now()->format('Y-m');

        $this->actingAs(User::factory()->create(['role' => 'registrar']))
            ->get(route('registrar.availability.month') . '?office=Registrar&month=' . $month)
            ->assertOk()
            ->assertJsonStructure([now()->toDateString() => ['past', 'available', 'open', 'status']]);
    }

    public function test_booking_and_reschedule_pages_render_availability_calendar(): void
    {
        $student = $this->makeStudent();

        $this->actingAs($student)
            ->get(route('student.appointments.create'))
            ->assertOk()
            ->assertSee('Preferred Date')
            ->assertSee('Fully booked / Blocked')
            ->assertSee('x-data="{', false);

        $booking = $this->makeStudent()->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Enrollment',
            'date' => now()->addDays(3)->toDateString(),
            'time_slot' => '09:00 AM - 10:00 AM',
            'status' => AppointmentStatus::CONFIRMED->value,
        ]);

        $this->actingAs(User::factory()->create(['role' => 'registrar']))
            ->get(route('registrar.appointments.reschedule', $booking))
            ->assertOk()
            ->assertSee('New Date')
            ->assertSee('Fully booked / Blocked');
    }
}