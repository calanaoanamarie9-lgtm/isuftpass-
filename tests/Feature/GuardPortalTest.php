<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\DocumentRequestStatus;
use App\Models\Appointment;
use App\Models\DocumentRequest;
use App\Models\GateLog;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * The campus security desk.
 *
 * Three things have to hold at the gate: a scan resolves the one pass it
 * carries and nothing else, only a genuinely valid pass opens the gate, and
 * every verified scan leaves an entry and an exit behind for the day.
 */
class GuardPortalTest extends TestCase
{
    use RefreshDatabase;

    private function guard(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_GUARD,
            'office' => 'Main Gate',
        ]);
    }

    private function student(): User
    {
        return User::factory()->create(['role' => User::ROLE_STUDENT]);
    }

    private function visitor(array $overrides = []): Visitor
    {
        return Visitor::factory()->create($overrides);
    }

    private function claimPass(
        User $student,
        string $status = DocumentRequestStatus::READY_FOR_PICKUP->value,
        string $name = 'Claim Owner',
    ): DocumentRequest {
        return $student->documentRequests()->create([
            'student_name' => $name,
            'student_address' => 'Iloilo City',
            'student_contact' => '09170000000',
            'student_course_year' => 'BSIT 3',
            'status' => $status,
            'purpose_type' => 'employment',
            'educational_status' => 'not_graduated',
            'educational_level' => 'college',
            'claim_mode' => 'personal',
            'submitted_at' => now(),
        ]);
    }

    private function appointment(User $student, string $date, string $status = 'confirmed'): Appointment
    {
        return $student->appointments()->create([
            'office' => 'Registrar',
            'purpose' => 'Enrollment',
            'date' => $date,
            'time_slot' => '09:00 AM - 10:00 AM',
            'status' => $status,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Access
    |--------------------------------------------------------------------------
    */

    public function test_the_scanner_is_the_guard_dashboard(): void
    {
        $this->actingAs($this->guard())
            ->get('/guard')
            ->assertOk()
            ->assertSee('Gate Scanner & Check-in')
            ->assertSee('Daily Gate Access Logs')
            ->assertSee('Start camera')
            ->assertSee('Main Gate');
    }

    public function test_the_scanner_page_renders_exactly_one_sidebar(): void
    {
        $response = $this->actingAs($this->guard())->get('/guard')->assertOk();

        $this->assertSame(
            1,
            substr_count($response->getContent(), '<aside'),
            'The guard scanner must render exactly one sidebar.'
        );
    }

    public function test_a_guard_lands_on_the_scanner_from_the_dashboard(): void
    {
        $this->actingAs($this->guard())
            ->get('/dashboard')
            ->assertRedirect('/guard');
    }

    public function test_only_a_guard_reaches_the_desk(): void
    {
        foreach (['student', 'admin', 'registrar', 'cashier', 'department', 'office'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)->get('/guard')->assertForbidden();
            $this->actingAs($user)->postJson('/guard/scan', ['token' => 'anything'])->assertForbidden();
            $this->actingAs($user)->get('/guard/visitors')->assertForbidden();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Scanning a Visitor Gate Pass
    |--------------------------------------------------------------------------
    */

    public function test_a_valid_visitor_pass_is_verified_and_opens_an_entry(): void
    {
        $visitor = $this->visitor([
            'name' => 'Maria Santos',
            'purpose' => 'Meeting with the Dean',
            'destination_office' => 'Registrar',
        ]);

        $this->actingAs($this->guard())
            ->postJson('/guard/scan', ['token' => $visitor->token])
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('pass.label', 'Visitor Gate Pass')
            ->assertJsonPath('pass.name', 'Maria Santos')
            ->assertJsonPath('pass.destination', 'Registrar')
            ->assertJsonPath('pass.purpose', 'Meeting with the Dean')
            ->assertJsonPath('outcome.direction', 'in');

        $log = GateLog::where('pass_type', 'visitor')
            ->where('pass_id', $visitor->id)
            ->firstOrFail();

        $this->assertSame('in', $log->direction);
        $this->assertNotNull($log->time_in);
        $this->assertNull($log->time_out);
        $this->assertSame('Maria Santos', $log->person_name);
        $this->assertSame($visitor->reference_code, $log->reference_code);
        $this->assertSame('Main Gate', $log->gate);
    }

    public function test_a_second_scan_closes_the_session_with_a_time_out(): void
    {
        $visitor = $this->visitor();
        $guard = $this->guard();

        $this->actingAs($guard)
            ->postJson('/guard/scan', ['token' => $visitor->token])
            ->assertJsonPath('outcome.direction', 'in');

        $this->travel(20)->minutes();

        $this->actingAs($guard)
            ->postJson('/guard/scan', ['token' => $visitor->token])
            ->assertJsonPath('outcome.direction', 'out');

        $logs = GateLog::where('pass_type', 'visitor')->where('pass_id', $visitor->id)->get();

        $this->assertCount(1, $logs, 'A second scan closes the open session instead of opening a new row.');
        $this->assertNotNull($logs->first()->time_out);
        $this->assertSame('out', $logs->first()->direction);
    }

    public function test_a_third_scan_after_leaving_opens_a_fresh_session(): void
    {
        $visitor = $this->visitor();
        $guard = $this->guard();

        foreach (['in', 'out', 'in'] as $direction) {
            $this->actingAs($guard)
                ->postJson('/guard/scan', ['token' => $visitor->token])
                ->assertJsonPath('outcome.direction', $direction);

            $this->travel(5)->minutes();
        }

        $logs = GateLog::where('pass_type', 'visitor')->where('pass_id', $visitor->id)->get();

        $this->assertCount(2, $logs, 'A new arrival after a completed visit is a new session.');
        $this->assertSame(1, $logs->whereNull('time_out')->count());
    }

    public function test_the_desk_logs_and_shows_campus_time_not_utc(): void
    {
        // 01:00 UTC is 09:00 the same morning in the Philippines.
        $this->travelTo(Carbon::create(2026, 10, 13, 1, 0, 0, 'UTC'));

        $visitor = $this->visitor();

        $this->actingAs($this->guard())
            ->postJson('/guard/scan', ['token' => $visitor->token])
            ->assertJsonPath('outcome.timeIn', '9:00 AM')
            ->assertJsonPath('outcome.message', 'Entry recorded at 9:00 AM.');
    }

    public function test_a_scan_before_dawn_manila_counts_as_todays(): void
    {
        // 20:00 UTC on the 12th is 04:00 on the 13th in the Philippines.
        $this->travelTo(Carbon::create(2026, 10, 12, 20, 0, 0, 'UTC'));

        $visitor = $this->visitor();

        $this->actingAs($this->guard())
            ->postJson('/guard/scan', ['token' => $visitor->token])
            ->assertJsonPath('outcome.timeIn', '4:00 AM');

        // 02:00 UTC on the 13th is 10:00 the same campus morning, so the
        // 04:00 scan still belongs to today's log.
        $this->travelTo(Carbon::create(2026, 10, 13, 2, 0, 0, 'UTC'));

        $this->actingAs($this->guard())
            ->get('/guard')
            ->assertOk()
            ->assertSee($visitor->name);

        $this->assertSame(1, GateLog::count());
    }

    public function test_an_expired_visitor_pass_is_rejected_and_logs_nothing(): void
    {
        $visitor = $this->visitor(['valid_until' => now()->subDay()->toDateString()]);

        $this->actingAs($this->guard())
            ->postJson('/guard/scan', ['token' => $visitor->token])
            ->assertOk()
            ->assertJsonPath('valid', false)
            ->assertJsonPath('pass.statusLabel', 'Expired');

        $this->assertSame(0, GateLog::count());
    }

    public function test_a_revoked_visitor_pass_is_rejected_and_logs_nothing(): void
    {
        $visitor = $this->visitor(['status' => Visitor::STATUS_REVOKED]);

        $this->actingAs($this->guard())
            ->postJson('/guard/scan', ['token' => $visitor->token])
            ->assertOk()
            ->assertJsonPath('valid', false)
            ->assertJsonPath('pass.statusLabel', 'Revoked');

        $this->assertSame(0, GateLog::count());
    }

    public function test_an_unknown_qr_is_rejected_without_writing(): void
    {
        $this->actingAs($this->guard())
            ->postJson('/guard/scan', ['token' => 'no-such-token'])
            ->assertOk()
            ->assertJsonPath('valid', false)
            ->assertJsonPath('pass', null);

        $this->assertSame(0, GateLog::count());
    }

    public function test_a_pasted_verification_url_is_reduced_to_its_token(): void
    {
        $visitor = $this->visitor();

        $printed = 'https://isuftpass-9atj.onrender.com/verify/visitor/'.$visitor->token;

        $this->actingAs($this->guard())
            ->postJson('/guard/scan', ['token' => $printed])
            ->assertJsonPath('valid', true)
            ->assertJsonPath('pass.reference', $visitor->reference_code);
    }

    /*
    |--------------------------------------------------------------------------
    | Scanning a Digital Claim Pass
    |--------------------------------------------------------------------------
    */

    public function test_a_ready_claim_pass_is_verified_and_shows_only_that_request(): void
    {
        $student = $this->student();
        $student->studentProfile()->create(['student_id' => '2021-00123']);

        $ready = $this->claimPass($student, DocumentRequestStatus::READY_FOR_PICKUP->value, 'Maria Santos');
        $this->claimPass($student, DocumentRequestStatus::PROCESSING->value, 'Pedro Penduko');

        $this->actingAs($this->guard())
            ->postJson('/guard/scan', ['token' => $ready->claim_token])
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('pass.label', 'Digital Claim Pass')
            ->assertJsonPath('pass.name', 'Maria Santos')
            ->assertJsonPath('pass.personId', '2021-00123')
            ->assertJsonPath('pass.statusLabel', 'Ready for Pick-up')
            // The scan resolves exactly one request; the student's other
            // request must not leak into the payload.
            ->assertDontSee('Pedro Penduko');

        $this->assertDatabaseHas('gate_logs', [
            'pass_type' => 'document',
            'pass_id' => $ready->id,
            'person_name' => 'Maria Santos',
            'direction' => 'in',
        ]);
    }

    public function test_a_claim_pass_that_is_not_ready_is_rejected_and_logs_nothing(): void
    {
        $student = $this->student();
        $pending = $this->claimPass($student, DocumentRequestStatus::SUBMITTED->value);

        $this->actingAs($this->guard())
            ->postJson('/guard/scan', ['token' => $pending->claim_token])
            ->assertOk()
            ->assertJsonPath('valid', false)
            ->assertJsonPath('pass.statusLabel', 'Submitted');

        $this->assertSame(0, GateLog::count());
    }

    /*
    |--------------------------------------------------------------------------
    | Scanning an appointment pass / Digital ID
    |--------------------------------------------------------------------------
    */

    public function test_a_todays_appointment_is_valid_and_another_day_is_not(): void
    {
        $guard = $this->guard();
        $student = $this->student();

        $today = $this->appointment($student, now()->toDateString());

        $this->actingAs($guard)
            ->postJson('/guard/scan', ['token' => $today->qr_token])
            ->assertJsonPath('valid', true)
            ->assertJsonPath('pass.label', 'Appointment Visit Pass')
            ->assertJsonPath('pass.destination', 'Registrar');

        $tomorrow = $this->appointment($student, now()->addDay()->toDateString());

        $this->actingAs($guard)
            ->postJson('/guard/scan', ['token' => $tomorrow->qr_token])
            ->assertJsonPath('valid', false)
            ->assertJsonPath('pass.statusLabel', 'Scheduled '.now()->addDay()->format('M j, Y'));
    }

    public function test_the_guard_scan_never_moves_the_appointment_status(): void
    {
        $student = $this->student();
        $appointment = $this->appointment($student, now()->toDateString());

        $this->actingAs($this->guard())
            ->postJson('/guard/scan', ['token' => $appointment->qr_token])
            ->assertJsonPath('outcome.direction', 'in');

        $fresh = $appointment->fresh();

        // The guard logs a gate visit; the office still owns the check-in.
        $this->assertSame(AppointmentStatus::CONFIRMED->value, $fresh->status);
        $this->assertNull($fresh->checked_in_at);
    }

    public function test_a_digital_id_qr_is_verified(): void
    {
        $student = $this->student();

        $profile = $student->studentProfile()->create([
            'pass_token' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
            'student_id' => '2021-00999',
        ]);

        $this->actingAs($this->guard())
            ->postJson('/guard/scan', ['token' => $profile->pass_token])
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('pass.label', 'Digital ID')
            ->assertJsonPath('pass.name', $student->name)
            ->assertJsonPath('pass.personId', '2021-00999');
    }

    /*
    |--------------------------------------------------------------------------
    | The daily access log
    |--------------------------------------------------------------------------
    */

    public function test_the_daily_log_can_be_searched_by_name_or_reference(): void
    {
        $guard = $this->guard();

        $maria = $this->visitor(['name' => 'Maria Santos']);
        $pedro = $this->visitor(['name' => 'Pedro Penduko']);

        $this->actingAs($guard)->postJson('/guard/scan', ['token' => $maria->token]);
        $this->actingAs($guard)->postJson('/guard/scan', ['token' => $pedro->token]);

        $this->actingAs($guard)
            ->get('/guard?q=Maria')
            ->assertOk()
            ->assertSee('Maria Santos')
            ->assertDontSee('Pedro Penduko');

        $this->actingAs($guard)
            ->get('/guard?q='.$maria->reference_code)
            ->assertOk()
            ->assertSee('Maria Santos')
            ->assertDontSee('Pedro Penduko');
    }

    /*
    |--------------------------------------------------------------------------
    | Issuing and revoking Visitor Gate Passes
    |--------------------------------------------------------------------------
    */

    public function test_a_guard_can_issue_a_visitor_pass(): void
    {
        $this->actingAs($this->guard())
            ->post('/guard/visitors', [
                'name' => 'Juan Dela Cruz',
                'purpose' => 'Delivery',
                'destination_office' => 'Registrar',
                'contact_number' => '09171234567',
            ])
            ->assertRedirect();

        $visitor = Visitor::where('name', 'Juan Dela Cruz')->firstOrFail();

        $this->assertStringStartsWith('VIS-', $visitor->reference_code);
        $this->assertNotNull($visitor->token);
        $this->assertTrue($visitor->isValid());

        $this->actingAs($this->guard())
            ->get('/guard/visitors/'.$visitor->id)
            ->assertOk()
            ->assertSee('Juan Dela Cruz')
            ->assertSee($visitor->reference_code)
            ->assertSee('Visitor Gate Pass');
    }

    public function test_issuing_requires_a_known_destination(): void
    {
        $this->actingAs($this->guard())
            ->from('/guard/visitors')
            ->post('/guard/visitors', [
                'name' => 'Juan Dela Cruz',
                'purpose' => 'Delivery',
                'destination_office' => 'Nowhere Land',
            ])
            ->assertSessionHasErrors('destination_office');

        $this->assertSame(0, Visitor::count());
    }

    public function test_revoking_a_pass_stops_it_from_working(): void
    {
        $visitor = $this->visitor();

        $this->actingAs($this->guard())
            ->post('/guard/visitors/'.$visitor->id.'/revoke')
            ->assertRedirect();

        $this->assertSame(Visitor::STATUS_REVOKED, $visitor->fresh()->status);

        $this->actingAs($this->guard())
            ->postJson('/guard/scan', ['token' => $visitor->token])
            ->assertJsonPath('valid', false);
    }

    /*
    |--------------------------------------------------------------------------
    | Admin accounts the desk
    |--------------------------------------------------------------------------
    */

    public function test_an_admin_can_create_a_guard_account(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'Gate Guard',
                'email' => 'gate.guard@isufst.edu.ph',
                'role' => User::ROLE_GUARD,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'gate.guard@isufst.edu.ph',
            'role' => User::ROLE_GUARD,
        ]);
    }
}
