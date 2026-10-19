<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ConsultationService;
use App\Support\CheckInList;
use App\Support\TimeSlots;
use App\Support\WorkspaceDashboard;
use Illuminate\View\View;

class CoagController extends Controller
{
    public function dashboard(): View
    {
        return view('departments.COAG.dashboard', WorkspaceDashboard::data(
            auth()->user()->officeScope()
        ));
    }

    public function appointments(): View
    {
        $user = auth()->user();
        $office = $user->officeScope();

        $appointments = Appointment::with('user')
            ->where('office', $office)
            ->firstComeFirstServed()
            ->paginate(12);

        return view('departments.COAG.appointments', [
            'office' => $office,
            'appointments' => $appointments,
        ]);
    }

    public function availability(): View
    {
        $user = auth()->user();
        $office = $user->officeScope();

        $timeSlots = TimeSlots::forOffice($office);

        return view('departments.COAG.availability', [
            'office' => $office,
            'timeSlots' => $timeSlots,
        ]);
    }

    public function qrScanner(): View
    {
        $user = auth()->user();
        $office = $user->officeScope();

        return view('departments.COAG.qr', [
            'office' => $office,
            'checkIns' => CheckInList::today($office),
        ]);
    }

    public function consultations(): View
    {
        $user = auth()->user();
        $office = $user->officeScope();

        return view('departments.COAG.consultations', [
            'office' => $office,
            'services' => ConsultationService::forOffice($office)->ordered()->get(),
        ]);
    }

    public function help(): View
    {
        $user = auth()->user();
        $office = $user->officeScope();

        return view('departments.COAG.help', [
            'office' => $office,
        ]);
    }
}
