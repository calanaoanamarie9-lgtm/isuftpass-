<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ConsultationService;
use App\Support\CheckInList;
use App\Support\WorkspaceDashboard;
use Illuminate\View\View;

class CoedController extends Controller
{
    public function dashboard(): View
    {
        return view('departments.COED.dashboard', WorkspaceDashboard::data(
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

        return view('departments.COED.appointments', [
            'office' => $office,
            'appointments' => $appointments,
        ]);
    }

    public function availability(): View
    {
        $user = auth()->user();
        $office = $user->officeScope();

        return view('departments.COED.availability', [
            'office' => $office,
        ]);
    }

    public function qrScanner(): View
    {
        $user = auth()->user();
        $office = $user->officeScope();

        return view('departments.COED.qr', [
            'office' => $office,
            'checkIns' => CheckInList::today($office),
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

    public function help(): View
    {
        $user = auth()->user();
        $office = $user->officeScope();

        return view('departments.COED.help', [
            'office' => $office,
        ]);
    }
}
