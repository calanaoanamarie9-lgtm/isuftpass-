<?php

namespace App\Http\Controllers\Offices;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ConsultationService;
use App\Support\CheckInList;
use App\Support\TimeSlots;
use App\Support\WorkspaceDashboard;
use Illuminate\View\View;

class AccountingController extends Controller
{
    public function dashboard(): View
    {
        return view('Offices.Accounting.dashboard', WorkspaceDashboard::data('Accounting'));
    }

    public function appointments(): View
    {
        return view('Offices.Accounting.appointments', [
            'office' => 'Accounting',
            'appointments' => Appointment::with('user')
                ->where('office', 'Accounting')
                ->firstComeFirstServed()
                ->paginate(12),
        ]);
    }

    public function availability(): View
    {
        return view('Offices.Accounting.availability', [
            'office' => 'Accounting',
            'timeSlots' => TimeSlots::forOffice('Accounting'),
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
