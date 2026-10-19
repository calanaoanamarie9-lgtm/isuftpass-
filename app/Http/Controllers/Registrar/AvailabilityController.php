<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Support\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    /**
     * The office managed by the logged-in staff account.
     */
    private function officeKey(): string
    {
        return auth()->user()?->officeScope() ?? 'Registrar';
    }

    /**
     * Availability page — the office's hours.
     *
     * This page once also managed the office's bookable *slots* and a
     * per-date open/closed calendar. Slots are gone: a booking names a day
     * and the office answers with the window it wants the student in when
     * it approves, so all that remains to manage here are the hours that
     * bound an approval.
     */
    public function index(Request $request): View
    {
        return view('registrar.availability', [
            'office' => Office::where('name', $this->officeKey())->first(),
        ]);
    }

    /**
     * Save the office's opening and closing time.
     */
    public function saveHours(Request $request): JsonResponse
    {
        $data = $request->validate([
            'open_time' => ['required', 'date_format:H:i'],
            'close_time' => ['required', 'date_format:H:i', 'after:open_time'],
        ]);

        $office = Office::where('name', $this->officeKey())->firstOrFail();

        $office->open_time = $data['open_time'];
        $office->close_time = $data['close_time'];
        $office->save();

        AuditLogger::log(
            'office.hours',
            'Set office hours to '.$data['open_time'].' - '.$data['close_time'].' for '.$office->name.'.'
        );

        return response()->json([
            'message' => $office->name.' is now open '.$data['open_time'].' - '.$data['close_time'].'.',
        ]);
    }
}
