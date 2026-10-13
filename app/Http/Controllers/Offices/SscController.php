<?php

namespace App\Http\Controllers\Offices;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ConsultationService;
use App\Support\CheckInList;
use App\Support\TimeSlots;
use App\Support\WorkspaceDashboard;
use Illuminate\View\View;

class SscController extends Controller
{
    private const OFFICE = 'SSC';

    public function dashboard(): View
    {
        return view('Offices.SSC.dashboard', WorkspaceDashboard::data(self::OFFICE));
    }

    public function appointments(): View
    {
        return view('Offices.SSC.appointments', [
            'office' => self::OFFICE,
            'appointments' => Appointment::with('user')
                ->where('office', self::OFFICE)
                ->firstComeFirstServed()
                ->paginate(12),
        ]);
    }

    public function availability(): View
    {
        return view('Offices.SSC.availability', [
            'office' => self::OFFICE,
            'timeSlots' => TimeSlots::forOffice(self::OFFICE),
        ]);
    }

    public function qrScanner(): View
    {
        return view('Offices.SSC.qr', [
            'office' => self::OFFICE,
            'checkIns' => CheckInList::today(self::OFFICE),
        ]);
    }

    public function consultations(): View
    {
        return view('Offices.SSC.consultations', [
            'office' => self::OFFICE,
            'services' => ConsultationService::forOffice(self::OFFICE)->ordered()->get(),
        ]);
    }

    public function help(): View
    {
        return view('Offices.SSC.help', [
            'office' => self::OFFICE,
        ]);
    }
}
