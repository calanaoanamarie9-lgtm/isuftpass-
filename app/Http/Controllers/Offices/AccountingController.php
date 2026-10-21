<?php

namespace App\Http\Controllers\Offices;

use App\Http\Controllers\Controller;
use App\Models\ConsultationService;
use App\Support\AppointmentList;
use App\Support\AvailabilityPage;
use App\Support\CheckInList;
use App\Support\WorkspaceDashboard;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountingController extends Controller
{
    public function dashboard(): View
    {
        return view('Offices.Accounting.dashboard', WorkspaceDashboard::data('Accounting'));
    }

    public function appointments(Request $request): View
    {
        return view('registrar.appointments.index', [
            ...AppointmentList::for(request()->user(), $request),
            'prefix' => 'accounting',
        ]);
    }

    public function availability(Request $request): View
    {
        return view('registrar.availability', [
            ...AvailabilityPage::for('Accounting', $request->input('month')),
            'prefix' => 'accounting',
        ]);
    }

    public function qrScanner(): View
    {
        return view('Offices.Accounting.qr', [
            'office' => 'Accounting',
            'checkIns' => CheckInList::today('Accounting'),
        ]);
    }

    public function consultations(): View
    {
        return view('Offices.Accounting.consultations', [
            'office' => 'Accounting',
            'services' => ConsultationService::forOffice('Accounting')->ordered()->get(),
        ]);
    }

    public function help(): View
    {
        return view('Offices.Accounting.help', [
            'office' => 'Accounting',
        ]);
    }
}
