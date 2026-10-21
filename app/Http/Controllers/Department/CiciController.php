<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Models\ConsultationService;
use App\Support\AppointmentList;
use App\Support\AvailabilityPage;
use App\Support\CheckInList;
use App\Support\WorkspaceDashboard;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CiciController extends Controller
{
    public function dashboard(): View
    {
        return view('departments.CICI.dashboard', WorkspaceDashboard::data(
            auth()->user()->officeScope()
        ));
    }

    public function appointments(Request $request): View
    {
        return view('registrar.appointments.index', [
            ...AppointmentList::for(request()->user(), $request),
            'prefix' => 'cici',
        ]);
    }

    public function availability(Request $request): View
    {
        return view('registrar.availability', [
            ...AvailabilityPage::for(auth()->user()->officeScope(), $request->input('month')),
            'prefix' => 'cici',
        ]);
    }

    public function qrScanner(): View
    {
        $user = auth()->user();
        $office = $user->officeScope();

        return view('departments.CICI.qr', [
            'office' => $office,
            'checkIns' => CheckInList::today($office),
        ]);
    }

    public function consultations(): View
    {
        $user = auth()->user();
        $office = $user->officeScope();

        return view('departments.CICI.consultations', [
            'office' => $office,
            'services' => ConsultationService::forOffice($office)->ordered()->get(),
        ]);
    }

    public function help(): View
    {
        $user = auth()->user();
        $office = $user->officeScope();

        return view('departments.CICI.help', [
            'office' => $office,
        ]);
    }
}
