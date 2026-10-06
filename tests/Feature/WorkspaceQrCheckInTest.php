<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * One view and one handler serve the QR desk of all eight offices and
 * departments, so what has to hold is the pair's contract: every desk can
 * check a student in with a single scan, the arrival is recorded with a time,
 * and no desk can write into another office's visits.
 */
class WorkspaceQrCheckInTest extends TestCase
{
    use RefreshDatabase;

    /** URL prefix => [office, role] for every workspace the scanner is served to. */
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

    private function makeAppointment(string $office, array $overrides = []): Appointment
    {
        $student = User::factory()->create(['role' => 'student']);

        return $student->appointments()->create(array_merge([
            'office' => $office,
            'purpose' => 'Certification of grades',
            'date' => now()->toDateString(),
            'time_slot' => Appointment::TIME_SLOTS[0],
            'status' => AppointmentStatus::CONFIRMED->value,
        ], $overrides));
    }

    public function test_the_scanner_page_is_served_to_every_office_and_department(): void
    {
        foreach (self::WORKSPACES as $prefix => [$office, $role]) {
            $this->actingAs($this->makeStaff($office, $role))
                ->get($prefix . '/qr')
                ->assertOk()
                ->assertSee('QR Scanner & Check-in')
                ->assertSee('Start camera')
                ->assertSee('Checked in today')
                // @js() prints the URL with escaped slashes: \/cici\/qr\/check-in.
                ->assertSee(str_replace('/', '\/', $prefix . '/qr/check-in'), false)
                ->assertSee($office);
        }
    }

    public function test_every_desk_checks_a_student_in_with_one_scan(): void
    {
        foreach (self::WORKSPACES as $prefix => [$office, $role]) {
            $appointment = $this->makeAppointment($office);

            $this->actingAs($this->makeStaff($office, $role))
                ->postJson($prefix . '/qr/check-in', ['token' => $appointment->qr_token])
                ->assertOk()
                ->assertJsonPath('valid', true)
                ->assertJsonPath('checkedIn', true)
                ->assertJsonPath('student', $appointment->user->name)
                ->assertJsonPath('office', $office);

            $fresh = $appointment->fresh();

            $this->assertSame(AppointmentStatus::CHECKED_IN->value, $fresh->status);
            $this->assertNotNull($fresh->checked_in_at, 'The arrival time must be recorded.');
            $this->assertSame($office, $fresh->office, 'Check-in must not move the visit.');
        }
    }

    public function test_a_pasted_verification_url_is_reduced_to_its_token(): void
    {
        $appointment = $this->makeAppointment('OSAS');
        $printed = 'https://isuftpass-9atj.onrender.com/verify/appointment/' . $appointment->qr_token;

        $this->actingAs($this->makeStaff('OSAS', 'osas'))
            ->postJson('/osas/qr/check-in', ['token' => $printed])
            ->assertOk()
            ->assertJsonPath('checkedIn', true);

        $this->assertSame(AppointmentStatus::CHECKED_IN->value, $appointment->fresh()->status);
    }

    public function test_scanning_twice_reports_the_first_arrival_and_keeps_it(): void
    {
        $appointment = $this->makeAppointment('OSAS');
        $staff = $this->makeStaff('OSAS', 'osas');

        $this->actingAs($staff)
            ->postJson('/osas/qr/check-in', ['token' => $appointment->qr_token])
            ->assertJsonPath('checkedIn', true);

        $arrivedAt = $appointment->fresh()->checked_in_at;

        $this->travel(10)->minutes();

        $this->actingAs($staff)
            ->postJson('/osas/qr/check-in', ['token' => $appointment->qr_token])
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('checkedIn', true)
            ->assertJsonPath('message', 'Already checked in at ' . $arrivedAt->format('g:i A') . '.');

        $this->assertTrue(
            $appointment->fresh()->checked_in_at->equalTo($arrivedAt),
            'A second scan must not move the recorded arrival time.'
        );

        $this->travelBack();
    }

    public function test_a_desk_cannot_check_a_student_into_another_offices_visit(): void
    {
        $appointment = $this->makeAppointment('Guidance');

        $this->actingAs($this->makeStaff('OSAS', 'osas'))
            ->postJson('/osas/qr/check-in', ['token' => $appointment->qr_token])
            ->assertOk()
            ->assertJsonPath('valid', false)
            ->assertJsonPath('checkedIn', false)
            ->assertJsonPath('student', $appointment->user->name);

        $fresh = $appointment->fresh();

        $this->assertSame(AppointmentStatus::CONFIRMED->value, $fresh->status);
        $this->assertNull($fresh->checked_in_at);
    }

    public function test_a_students_digital_id_checks_them_into_todays_visit(): void
    {
        $appointment = $this->makeAppointment('Library');
        $appointment->user->studentProfile()->create(['pass_token' => 'pass-token-library-1']);

        $this->actingAs($this->makeStaff('Library', 'library'))
            ->postJson('/library/qr/check-in', ['token' => 'pass-token-library-1'])
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('checkedIn', true)
            ->assertJsonPath('student', $appointment->user->name);

        $this->assertSame(AppointmentStatus::CHECKED_IN->value, $appointment->fresh()->status);
    }

    public function test_a_digital_id_without_a_visit_today_says_so_and_writes_nothing(): void
    {
        $appointment = $this->makeAppointment('OSAS', ['date' => now()->addDays(2)->toDateString()]);
        $appointment->user->studentProfile()->create(['pass_token' => 'pass-token-osas-later']);

        $this->actingAs($this->makeStaff('OSAS', 'osas'))
            ->postJson('/osas/qr/check-in', ['token' => 'pass-token-osas-later'])
            ->assertOk()
            ->assertJsonPath('valid', false)
            ->assertJsonPath('checkedIn', false)
            ->assertJsonPath('message', $appointment->user->name . ' has no appointment with OSAS today.');

        $this->assertSame(AppointmentStatus::CONFIRMED->value, $appointment->fresh()->status);
    }

    public function test_an_unrecognised_qr_is_rejected(): void
    {
        $this->makeAppointment('COED');

        $this->actingAs($this->makeStaff('COED', 'department'))
            ->postJson('/coed/qr/check-in', ['token' => 'not-a-real-token'])
            ->assertOk()
            ->assertJsonPath('valid', false)
            ->assertJsonPath('checkedIn', false)
            ->assertJsonPath('message', 'This QR does not match any appointment or student pass.');
    }

    public function test_a_cancelled_visit_is_reported_not_overwritten(): void
    {
        $appointment = $this->makeAppointment('COAG', [
            'status' => AppointmentStatus::CANCELLED->value,
        ]);

        $this->actingAs($this->makeStaff('COAG', 'department'))
            ->postJson('/coag/qr/check-in', ['token' => $appointment->qr_token])
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('checkedIn', false)
            ->assertJsonPath('status', 'Cancelled');

        $fresh = $appointment->fresh();

        $this->assertSame(AppointmentStatus::CANCELLED->value, $fresh->status);
        $this->assertNull($fresh->checked_in_at);
    }

    public function test_a_visit_for_another_day_is_not_checked_in_early(): void
    {
        $appointment = $this->makeAppointment('CBMSD', [
            'date' => now()->addDays(3)->toDateString(),
        ]);

        $this->actingAs($this->makeStaff('CBMSD', 'department'))
            ->postJson('/cbmsd/qr/check-in', ['token' => $appointment->qr_token])
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('checkedIn', false);

        $this->assertSame(
            AppointmentStatus::CONFIRMED->value,
            $appointment->fresh()->status,
            'A visit can only be checked in on its own date.'
        );
    }

    public function test_todays_list_shows_only_this_offices_arrivals(): void
    {
        $ours = $this->makeAppointment('OSAS');
        $theirs = $this->makeAppointment('Guidance');

        $this->actingAs($this->makeStaff('Guidance', 'guidance'))
            ->postJson('/guidance/qr/check-in', ['token' => $theirs->qr_token])
            ->assertJsonPath('checkedIn', true);

        $response = $this->actingAs($this->makeStaff('OSAS', 'osas'))
            ->postJson('/osas/qr/check-in', ['token' => $ours->qr_token])
            ->assertOk()
            ->assertJsonPath('checkedIn', true);

        $listed = collect($response->json('checkIns'));

        $this->assertCount(1, $listed, 'Only this desk arrivals may be listed.');
        $this->assertSame($ours->user->name, $listed->first()['student']);
        $this->assertArrayHasKey('time', $listed->first());
    }

    public function test_students_reach_no_check_in_endpoint(): void
    {
        $appointment = $this->makeAppointment('OSAS');

        $this->actingAs(User::factory()->create(['role' => 'student']))
            ->postJson('/osas/qr/check-in', ['token' => $appointment->qr_token])
            ->assertForbidden();

        $this->assertSame(AppointmentStatus::CONFIRMED->value, $appointment->fresh()->status);
    }
}
