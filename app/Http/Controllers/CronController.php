<?php

namespace App\Http\Controllers;

use App\Jobs\SendAppointmentReminders;
use App\Support\AppointmentSweep;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CronController extends Controller
{
    /**
     * Fired by an external scheduler (e.g. Render Cron) or a manual request.
     * Requires the X-Cron-Secret header to match CRON_SECRET.
     */
    public function reminders(Request $request): JsonResponse
    {
        $this->checkSecret($request);

        SendAppointmentReminders::dispatchSync();

        return response()->json(['ok' => true]);
    }

    /**
     * Fired every half hour by .github/workflows/auto-cancel.yml - Render
     * has no free cron tier, same as the reminders above.
     *
     * Cancels the bookings whose hour ran out with nobody checked in (see
     * App\Support\AppointmentSweep), so the lists agree with the gate
     * without waiting for someone to happen to scan the pass. The two QR
     * desks call the same sweep for the one booking under the scanner, so
     * what the guard reads is never a step behind what this reports.
     */
    public function autoCancel(Request $request): JsonResponse
    {
        $this->checkSecret($request);

        return response()->json([
            'ok' => true,
            'cancelled' => AppointmentSweep::run(),
        ]);
    }

    /**
     * Both endpoints are public routes by necessity - the scheduler has no
     * session to authenticate with - so the shared secret is the whole gate.
     */
    private function checkSecret(Request $request): void
    {
        $expected = config('app.cron_secret');

        $authorized = $expected !== null
            && hash_equals($expected, (string) $request->header('X-Cron-Secret', ''));

        if (! $authorized) {
            abort(403);
        }
    }
}
