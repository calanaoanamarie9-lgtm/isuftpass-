<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\Office as OfficeEnum;
use App\Models\Appointment;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Makes every one of the eight workspaces usable on a fresh database.
 *
 * Two gaps are closed here, and both are invisible until you open a workspace:
 *
 *  1. Registry rows. AvailabilityController resolves the office with
 *     Office::where('name', ...)->firstOrFail(), but only OSAS, Registrar,
 *     Guidance, Accounting, Cashier and Admin existed — so COED, CICI, COAG,
 *     CBMSD and Library would 404 the moment anyone saved availability. The
 *     enum already describes every office; this writes those descriptions into
 *     the table the rest of the app actually reads.
 *
 *  2. Appointments. The dashboard tiles are counts over appointments scoped to
 *     one office, so a clean database rendered eight dashboards of zeros with
 *     nothing to try the pages against.
 *
 * Both phases are additive and idempotent: existing Office rows and an office
 * that already has bookings are left exactly as they are, so running this
 * against a database holding real data changes nothing.
 */
class WorkspaceSeeder extends Seeder
{
    /**
     * Appointments each workspace should have. Chosen so every tile on the
     * dashboard has something in it: a pending one today, a confirmed one
     * ahead, and a completed one behind.
     */
    private const APPOINTMENTS_PER_WORKSPACE = 4;

    private const WORKSPACES = [
        'COED', 'CICI', 'COAG', 'CBMSD',
        'OSAS', 'Accounting', 'Library', 'Guidance',
    ];

    public function run(): void
    {
        $this->registerOfficeRows();

        $students = User::where('role', 'student')->orderBy('id')->get();

        if ($students->isEmpty()) {
            $this->command?->warn('No student accounts found — skipping appointment seeding.');

            return;
        }

        $this->seedAppointments($students);
    }

    /**
     * Give every workspace the Office row its availability save depends on.
     */
    private function registerOfficeRows(): void
    {
        $created = 0;

        foreach (self::WORKSPACES as $name) {
            if (Office::where('name', $name)->exists()) {
                continue;
            }

            $enum = OfficeEnum::tryFrom($name);
            $details = $enum?->details() ?? [];

            Office::create([
                'name' => $name,
                'location' => $details['location'] ?? null,
                'hours' => $details['hours'] ?? null,
                'description' => $details['description'] ?? null,
                'is_active' => true,
                'default_slot_capacity' => 8,
            ]);

            $created++;
        }

        if ($created > 0) {
            $this->command?->info("Registered {$created} missing office row(s).");
        }
    }

    /**
     * Top up any workspace that is short of demo appointments.
     */
    private function seedAppointments($students): void
    {
        $purposes = [
            'Enrollment clearance',
            'Certificate of registration',
            'Good moral certificate',
            'Transcript of records',
            'Practicum endorsement',
            'Schedule adjustment',
        ];

        $created = 0;
        $studentIndex = 0;

        foreach (self::WORKSPACES as $office) {
            $shortfall = self::APPOINTMENTS_PER_WORKSPACE - Appointment::where('office', $office)->count();

            if ($shortfall <= 0) {
                continue;
            }

            // Demo bookings must land in slots that office actually offers.
            $slots = \App\Support\TimeSlots::forOffice($office);

            for ($i = 0; $i < $shortfall; $i++) {
                $student = $students[$studentIndex++ % $students->count()];

                // Statuses spread across past, today and future so the
                // total / pending / today / completed tiles all land non-zero.
                [$offset, $status] = match ($i % self::APPOINTMENTS_PER_WORKSPACE) {
                    0 => [0, AppointmentStatus::PENDING->value],
                    1 => [2, AppointmentStatus::CONFIRMED->value],
                    2 => [-3, AppointmentStatus::COMPLETED->value],
                    default => [5, AppointmentStatus::PENDING->value],
                };

                $appointment = new Appointment([
                    'user_id' => $student->id,
                    'office' => $office,
                    'purpose' => $purposes[$i % count($purposes)],
                    'date' => now()->addDays($offset)->toDateString(),
                    'time_slot' => $slots[array_rand($slots)],
                    'status' => $status,
                ]);

                // DatabaseSeeder runs under WithoutModelEvents, so the creating
                // hook that fills these never fires — set them here or every
                // seeded appointment renders an unscannable pass.
                $appointment->reference_code = sprintf(
                    'APT-%s-%s',
                    now()->format('Y'),
                    strtoupper(Str::random(4))
                );
                $appointment->qr_token = (string) Str::uuid();

                if ($status === AppointmentStatus::COMPLETED->value) {
                    $appointment->completed_at = now()->subDays(3);
                }

                $appointment->save();
                $created++;
            }
        }

        if ($created > 0) {
            $this->command?->info("Seeded {$created} appointment(s) across the eight workspaces.");
        }
    }
}
