<?php

namespace App\Http\Controllers;

use App\Jobs\SendAppointmentReminders;
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
        $expected = config('app.cron_secret');

        $authorized = $expected !== null
            && hash_equals($expected, (string) $request->header('X-Cron-Secret', ''));

        if (! $authorized) {
            abort(403);
        }

        SendAppointmentReminders::dispatchSync();

        return response()->json(['ok' => true]);
    }
}