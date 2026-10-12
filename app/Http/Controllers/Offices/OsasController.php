<?php

namespace App\Http\Controllers\Offices;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ConsultationService;
use App\Support\CheckInList;
use App\Support\TimeSlots;
use App\Support\WorkspaceDashboard;
use Illuminate\View\View;

class OsasController extends Controller
{
    public function dashboard(): View
    {
        return view('Offices.OSAS.dashboard', WorkspaceDashboard::data('OSAS'));
    }

    public function appointments(): View
    {
        return view('Offices.OSAS.appointments', [
            'office' => 'OSAS',
            'appointments' => Appointment::with('user')
                ->where('office', 'OSAS')
                ->firstComeFirstServed()
                ->paginate(12),
        ]);
    }

    public function availability(): View
    {
        return view('Offices.OSAS.availability', [
            'office' => 'OSAS',
            'timeSlots' => TimeSlots::forOffice('OSAS'),
        ]);
    }

    public function qrScanner(): View
    {
        return view('Offices.OSAS.qr', [
            'office' => 'OSAS',
            'checkIns' => CheckInList::today('OSAS'),
        ]);
    }

    public function consultations(): View
    {
        return view('Offices.OSAS.consultations', [
            'office' => 'OSAS',
            'services' => ConsultationService::forOffice('OSAS')->ordered()->get(),
        ]);
    }

    public function help(): View
    {
        return view('Offices.OSAS.help', [
            'office' => 'OSAS',
        ]);
    }
}
