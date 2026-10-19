<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Office;
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
        '/ssc' => ['SSC', 'ssc'],
        '/accounting' => ['Accounting', 'accounting'],
        '/library' => ['Library', 'library'],
    ];

    private function makeStaff(string $office, string $role): User
    {
        return User::factory()->create(['role' => $role, 'office' => $office]);
    }

    /**
     * The hours endpoint resolves the row before writing, so a workspace needs
     * one in the registry. WorkspaceSeeder creates these on a fresh database;
     * the admin office form creates them in production.
     */
    private function registerOffice(string $name): Office
    {
        // The migration already seeds six offices, so creating outright would
        // collide on offices.name — only the missing ones get written.
        return Office::firstOrCreate(
            ['name' => $name],
            ['is_active' => true]
        );
    }

    private function makeAppointment(string $office): Appointment
    {
        $student = User::factory()->create(['role' => 'student']);

        return $student->appointments()->create([
            'office' => $office,
            'purpose' => 'Certification',
            'date' => now()->addDays(2)->toDateString(),
            'status' => AppointmentStatus::CONFIRMED->value,
        ]);
    }

    public function test_every_workspace_renders_its_pages_without_placeholders(): void
    {
        foreach (self::WORKSPACES as $prefix => [$office, $role]) {
            $staff = $this->makeStaff($office, $role);

            foreach (['/appointments', '/availability', '/qr'] as $page) {
                $this->actingAs($staff)
                    ->get($prefix.$page)
                    ->assertOk()
                    ->assertDontSee('will appear here', false)
                    ->assertSee($office);
            }
        }
    }

    /**
     * Every workspace page is a shared view that owns the <x-app-layout>,
     * plus a per-office wrapper that only includes it. When a wrapper
     * wrapped its include in the layout too, the page rendered two
     * sidebars — invisible to assertOk(), very visible to the user.
     */
    public function test_each_workspace_page_renders_exactly_one_sidebar(): void
    {
        $pages = array_merge(self::WORKSPACES, [
            '/workspace' => ['Registrar Extension', 'office'],
        ]);

        foreach ($pages as $prefix => [$office, $role]) {
            $staff = $this->makeStaff($office, $role);

            foreach (['/appointments', '/availability', '/qr'] as $page) {
                $response = $this->actingAs($staff)
                    ->get($prefix.$page)
                    ->assertOk();

                $this->assertSame(
                    1,
                    substr_count($response->getContent(), '<aside'),
                    "{$prefix}{$page} must render exactly one sidebar."
                );
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

    public function test_office_can_save_its_own_office_hours(): void
    {
        $this->registerOffice('SSC');

        $this->actingAs($this->makeStaff('SSC', 'ssc'))
            ->postJson('/ssc/availability/hours', [
                'open_time' => '09:00',
                'close_time' => '18:00',
            ])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $office = Office::where('name', 'SSC')->first();

        $this->assertSame('09:00', $office->open_time);
        $this->assertSame('18:00', $office->close_time);
    }

    public function test_department_saves_its_own_office_hours(): void
    {
        $this->registerOffice('COED');

        $this->actingAs($this->makeStaff('COED', 'department'))
            ->postJson('/coed/availability/hours', [
                'open_time' => '08:00',
                'close_time' => '17:00',
            ])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $office = Office::where('name', 'COED')->first();

        $this->assertSame('08:00', $office->open_time);
        $this->assertSame('17:00', $office->close_time);
    }

    public function test_workspace_cannot_reschedule_another_offices_appointment(): void
    {
        $appointment = $this->makeAppointment('Accounting');

        $this->actingAs($this->makeStaff('SSC', 'ssc'))
            ->putJson('/ssc/appointments/'.$appointment->id.'/reschedule', [
                'date' => now()->addDays(5)->toDateString(),
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

        $appointment = $this->makeAppointment('SSC');
        $newDate = now()->addDays(5)->toDateString();

        $this->actingAs($this->makeStaff('SSC', 'ssc'))
            ->put('/ssc/appointments/'.$appointment->id.'/reschedule', [
                'date' => $newDate,
                'reschedule_reason' => 'Student request',
            ])
            ->assertRedirect(route('ssc.appointments'));

        $this->assertSame(AppointmentStatus::RESCHEDULED->value, $appointment->fresh()->status);
    }

    public function test_registrar_reschedules_back_to_the_appointment_record(): void
    {
        Mail::fake();

        $appointment = $this->makeAppointment('Registrar');

        $this->actingAs(User::factory()->create(['role' => 'registrar']))
            ->put('/registrar/appointments/'.$appointment->id.'/reschedule', [
                'date' => now()->addDays(5)->toDateString(),
                'reschedule_reason' => 'Requested day cannot be accommodated',
            ])
            ->assertRedirect(route('registrar.appointments.show', $appointment));
    }

    public function test_student_cannot_reach_workspace_pages_or_endpoints(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $appointment = $this->makeAppointment('SSC');

        $this->actingAs($student)->get('/ssc/appointments')->assertForbidden();
        $this->actingAs($student)->get('/library/availability')->assertForbidden();
        $this->actingAs($student)->get('/accounting/qr')->assertForbidden();
        $this->actingAs($student)
            ->postJson('/ssc/availability/hours', ['open_time' => '09:00', 'close_time' => '18:00'])
            ->assertForbidden();
        $this->actingAs($student)
            ->putJson('/ssc/appointments/'.$appointment->id.'/reschedule', [
                'date' => now()->addDays(5)->toDateString(),
                'reschedule_reason' => 'Nope',
            ])
            ->assertForbidden();
    }
}
