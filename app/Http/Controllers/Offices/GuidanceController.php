<?php

namespace App\Http\Controllers\Offices;

use App\Http\Controllers\Controller;
use App\Models\ConsultationService;
use Illuminate\View\View;

class GuidanceController extends Controller
{
    public function dashboard(): View
    {
        return view('Offices.Guidance.dashboard', \App\Support\WorkspaceDashboard::data('Guidance'));
    }

    public function appointments(): View
    {
        return view('Offices.Guidance.appointments', [
            'office' => 'Guidance',
            'appointments' => \App\Models\Appointment::with('user')
                ->where('office', 'Guidance')
                ->latest()
                ->paginate(12),
        ]);
    }

    public function availability(): View
    {
        return view('Offices.Guidance.availability', [
            'office' => 'Guidance',
            'timeSlots' => \App\Support\TimeSlots::forOffice('Guidance'),
        ]);
    }

    public function qrScanner(): View
    {
        return view('Offices.Guidance.qr', [
            'office' => 'Guidance',
            'checkIns' => \App\Support\CheckInList::today('Guidance'),
        ]);
    }

    public function consultations(): View
    {
        return view('Offices.Guidance.consultations', [
            'office' => 'Guidance',
            'services' => ConsultationService::forOffice('Guidance')->ordered()->get(),
        ]);
    }

    public function profile(): View
    {
        return view('Offices.Guidance.profile', [
            'user' => auth()->user(),
            'office' => 'Guidance',
        ]);
    }

    public function help(): View
    {
        return view('Offices.Guidance.help', [
            'office' => 'Guidance',
        ]);
    }
}
