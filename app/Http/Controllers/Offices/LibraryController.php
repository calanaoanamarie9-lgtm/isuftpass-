<?php

namespace App\Http\Controllers\Offices;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ConsultationService;
use App\Support\CheckInList;
use App\Support\TimeSlots;
use App\Support\WorkspaceDashboard;
use Illuminate\View\View;

class LibraryController extends Controller
{
    public function dashboard(): View
    {
        return view('Offices.Library.dashboard', WorkspaceDashboard::data('Library'));
    }

    public function appointments(): View
    {
        return view('Offices.Library.appointments', [
            'office' => 'Library',
            'appointments' => Appointment::with('user')
                ->where('office', 'Library')
                ->firstComeFirstServed()
                ->paginate(12),
        ]);
    }

    public function availability(): View
    {
        return view('Offices.Library.availability', [
            'office' => 'Library',
            'timeSlots' => TimeSlots::forOffice('Library'),
        ]);
    }

    public function qrScanner(): View
    {
        return view('Offices.Library.qr', [
            'office' => 'Library',
            'checkIns' => CheckInList::today('Library'),
        ]);
    }

    public function consultations(): View
    {
        return view('Offices.Library.consultations', [
            'office' => 'Library',
            'services' => ConsultationService::forOffice('Library')->ordered()->get(),
        ]);
    }

    public function help(): View
    {
        return view('Offices.Library.help', [
            'office' => 'Library',
        ]);
    }
}
