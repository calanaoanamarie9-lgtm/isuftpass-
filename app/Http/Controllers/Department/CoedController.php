<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Models\ConsultationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoedController extends Controller
{
    public function dashboard(): View
    {
        return view('departments.COED.dashboard', \App\Support\WorkspaceDashboard::data(
            auth()->user()->officeScope()
        ));
    }

    public function appointments(): View
    {
        $user = auth()->user();
        $office = $user->officeScope();

        $appointments = \App\Models\Appointment::with('user')
            ->where('office', $office)
            ->latest()
            ->paginate(12);

        return view('departments.COED.appointments', [
            'office' => $office,
            'appointments' => $appointments,
        ]);
    }

    public function availability(): View
    {
        $user = auth()->user();
        $office = $user->officeScope();

        $timeSlots = \App\Support\TimeSlots::forOffice($office);

        return view('departments.COED.availability', [
            'office' => $office,
            'timeSlots' => $timeSlots,
        ]);
    }

    public function qrScanner(): View
    {
        $user = auth()->user();
        $office = $user->officeScope();

        return view('departments.COED.qr', [
            'office' => $office,
            'checkIns' => \App\Support\CheckInList::today($office),
        ]);
    }

    public function consultations(): View
    {
        $user = auth()->user();
        $office = $user->officeScope();

        return view('departments.COED.consultations', [
            'office' => $office,
            'services' => ConsultationService::forOffice($office)->ordered()->get(),
        ]);
    }

    public function profile(): View
    {
        $user = auth()->user();
        $office = $user->officeScope();

        return view('departments.COED.profile', [
            'user' => $user,
            'office' => $office,
        ]);
    }

    public function help(): View
    {
        $user = auth()->user();
        $office = $user->officeScope();

        return view('departments.COED.help', [
            'office' => $office,
        ]);
    }
}