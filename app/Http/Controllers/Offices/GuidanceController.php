<?php

namespace App\Http\Controllers\Offices;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ConsultationService;
use App\Support\CheckInList;
use App\Support\TimeSlots;
use App\Support\WorkspaceDashboard;
use Illuminate\View\View;

class GuidanceController extends Controller
{
    public function dashboard(): View
    {
        return view('Offices.Guidance.dashboard', WorkspaceDashboard::data('Guidance'));
    }

    public function appointments(): View
    {
        return view('Offices.Guidance.appointments', [
            'office' => 'Guidance',
            'appointments' => Appointment::with('user')
                ->where('office', 'Guidance')
                ->firstComeFirstServed()
                ->paginate(12),
        ]);
    }

    public function availability(): View
    {
        return view('Offices.Guidance.availability', [
            'office' => 'Guidance',
            'timeSlots' => TimeSlots::forOffice('Guidance'),
        ]);
    }

    public function qrScanner(): View
    {
        return view('Offices.Guidance.qr', [
            'office' => 'Guidance',
            'checkIns' => CheckInList::today('Guidance'),
        ]);
    }

    public function consultations(): View
    {
        return view('Offices.Guidance.consultations', [
            'office' => 'Guidance',
            'services' => ConsultationService::forOffice('Guidance')->ordered()->get(),
        ]);
    }

    public function help(): View
    {
        return view('Offices.Guidance.help', [
            'office' => 'Guidance',
        ]);
    }
}
