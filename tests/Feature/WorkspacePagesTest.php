<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Office;
use App\Models\SlotAvailability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The eight workspaces share one set of views and one set of handlers, so this
 * covers what that sharing is supposed to buy: every workspace renders the same
 * real pages, and the endpoints they borrowed from the registrar now pin the
 * caller to its own office.
 */
class WorkspacePagesTest extends TestCase
{
    use RefreshDatabase;

    /** URL prefix => [office, role] for every workspace in the application. */
    private const WORKSPACES = [
        '/cici' => ['CICI', 'department'],
        '/cbmsd' => ['CBMSD', 'department'],
        '/coag' => ['COAG', 'department'],
        '/coed' => ['COED', 'department'],
        '/osas' => ['OSAS', 'osas'],
        '/accounting' => ['Accounting', 'accounting'],
        '/library' => ['Library', 'library'],
        '/guidance' => ['Guidance', 'guidance'],
    ];

    private function makeStaff(string $office, string $role): User
    {
        return User::factory()->create(['role' => $role, 'office' => $office]);
    }

    /**
     * AvailabilityController resolves the row before writing, so a workspace
     * needs one in the registry. WorkspaceSeeder creates these on a fresh
     * database; the admin office form creates them in production.
     */
    private function registerOffice(string $name): Office
    {
        // The migration already seeds six offices, so creating outright would
        // collide on offices.name — only the missing ones get written.
        return Office::firstOrCreate(
            ['name' => $name],
            ['is_active' => true, 'default_slot_capacity' => 8]
        );
    }

    private function makeAppointment(string $office): Appointment
    {
        $student = User::factory()->create(['role' => 'student']);

        return $student->appointments()->create([
            'office' => $office,
            'purpose' => 'Certification',
            'date' => now()->addDays(2)->toDateString(),
            'time_slot' => Appointment::TIME_SLOTS[1],
            'status' => AppointmentStatus::CONFIRMED->value,
        ]);
    }

    public function test_every_workspace_renders_its_pages_without_placeholders(): void
    {
        foreach (self::WORKSPACES as $prefix => [$office, $role]) {
            $staff = $this->makeStaff($office, $role);

            foreach (['/appointments', '/availability', '/qr'] as $page) {
                $this->actingAs($staff)
                    ->get($prefix . $page)
                    ->assertOk()
                    ->assertDontSee('will appear here', false)
                    ->assertSee($office);
            }
        }
    }

    /**
     * The dashboard root is the one page the test above never hits - each
     * group's own controller feeding the shared tiles, plus the generic
     * workspace any approved office outside the eight built-ins lands in.
     */
    public function test_every_workspace_dashboard_renders(): void
    {
        foreach (self::WORKSPACES as $prefix => [$office, $role]) {
            $this->actingAs($this->makeStaff($office, $role))
                ->get($prefix)
                ->assertOk()
                ->assertSee($office);
        }

        $this->actingAs($this->makeStaff('Micro-Fisheries Extension', 'office'))
            ->get('/workspace')
            ->assertOk()
            ->assertSee('Micro-Fisheries Extension');
    }

    public function test_office_can_save_availability_for_its_own_office(): void
    {
        $office = $this->registerOffice('OSAS');
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($this->makeStaff('OSAS', 'osas'))
            ->postJson('/osas/availability/save', [
                'date' => $date,
                'type' => 'open',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'OSAS availability has been updated.');

        $rules = SlotAvailability::whereDate('date', $date)->get();

        $this->assertCount(count(Appointment::TIME_SLOTS), $rules);
        $this->assertTrue(
            $rules->every(fn (SlotAvailability $rule) => $rule->office_id === $office->id),
            'Every rule written by a workspace must belong to that workspace.'
        );
    }

    public function test_department_saves_availability_under_its_own_name(): void
    {
        $this->registerOffice('COED');
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($this->makeStaff('COED', 'department'))
            ->postJson('/coed/availability/save', [
                'date' => $date,
                'type' => 'closed',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'COED availability has been updated.');
    }

    public function test_workspace_cannot_read_another_offices_slot_capacity(): void
    {
        $date = now()->addDays(3)->toDateString();

        $this->actingAs($this->makeStaff('OSAS', 'osas'))
            ->getJson("/osas/appointments/slots?office=Guidance&date={$date}")
            ->assertForbidden();

        $this->actingAs($this->makeStaff('OSAS', 'osas'))
            ->getJson("/osas/appointments/slots?office=OSAS&date={$date}")
            ->assertOk();
    }

    public function test_registrar_can_read_any_offices_slot_capacity(): void
    {
        $date = now()->addDays(3)->toDateString();

        $this->actingAs(User::factory()->create(['role' => 'registrar']))
            ->getJson("/registrar/appointments/slots?office=Guidance&date={$date}")
            ->assertOk();
    }

    public function test_workspace_cannot_reschedule_another_offices_appointment(): void
    {
        $appointment = $this->makeAppointment('Guidance');

        $this->actingAs($this->makeStaff('OSAS', 'osas'))
            ->putJson('/osas/appointments/' . $appointment->id . '/reschedule', [
                'date' => now()->addDays(5)->toDateString(),
                'time_slot' => Appointment::TIME_SLOTS[2],
                'reschedule_reason' => 'Class conflict',
            ])
            ->assertForbidden();

        $this->assertSame(
            AppointmentStatus::CONFIRMED->value,
            $appointment->fresh()->status,
            'A 403 must be raised before the appointment is touched.'
        );
    }

    public function test_workspace_reschedules_back_to_its_own_appointment_list(): void
    {
        Mail::fake();

        $appointment = $this->makeAppointment('OSAS');
        $newDate = now()->addDays(5)->toDateString();

        $this->actingAs($this->makeStaff('OSAS', 'osas'))
            ->put('/osas/appointments/' . $appointment->id . '/reschedule', [
                'date' => $newDate,
                'time_slot' => Appointment::TIME_SLOTS[3],
                'reschedule_reason' => 'Student request',
            ])
            ->assertRedirect(route('osas.appointments'));

        $this->assertSame(AppointmentStatus::RESCHEDULED->value, $appointment->fresh()->status);
    }

    public function test_registrar_reschedules_back_to_the_appointment_record(): void
    {
        Mail::fake();

        $appointment = $this->makeAppointment('Registrar');

        $this->actingAs(User::factory()->create(['role' => 'registrar']))
            ->put('/registrar/appointments/' . $appointment->id . '/reschedule', [
                'date' => now()->addDays(5)->toDateString(),
                'time_slot' => Appointment::TIME_SLOTS[3],
                'reschedule_reason' => 'Slot limit reached',
            ])
            ->assertRedirect(route('registrar.appointments.show', $appointment));
    }

    public function test_student_cannot_reach_workspace_pages_or_endpoints(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $appointment = $this->makeAppointment('OSAS');

        $this->actingAs($student)->get('/osas/appointments')->assertForbidden();
        $this->actingAs($student)->get('/library/availability')->assertForbidden();
        $this->actingAs($student)->get('/guidance/qr')->assertForbidden();
        $this->actingAs($student)
            ->postJson('/osas/availability/save', ['date' => now()->addDays(3)->toDateString(), 'type' => 'open'])
            ->assertForbidden();
        $this->actingAs($student)
            ->getJson('/osas/appointments/slots?office=OSAS&date=' . now()->addDays(3)->toDateString())
            ->assertForbidden();
        $this->actingAs($student)
            ->putJson('/osas/appointments/' . $appointment->id . '/reschedule', [
                'date' => now()->addDays(5)->toDateString(),
                'time_slot' => Appointment::TIME_SLOTS[3],
                'reschedule_reason' => 'Nope',
            ])
            ->assertForbidden();
    }
}
