<?php

namespace App\Http\Controllers\Offices;

use App\Http\Controllers\Controller;
use App\Models\ConsultationService;
use Illuminate\View\View;

class OsasController extends Controller
{
    public function dashboard(): View
    {
        return view('Offices.OSAS.dashboard', \App\Support\WorkspaceDashboard::data('OSAS'));
    }

    public function appointments(): View
    {
        return view('Offices.OSAS.appointments', [
            'office' => 'OSAS',
            'appointments' => \App\Models\Appointment::with('user')
                ->where('office', 'OSAS')
                ->latest()
                ->paginate(12),
        ]);
    }

    public function availability(): View
    {
        return view('Offices.OSAS.availability', [
            'office' => 'OSAS',
            'timeSlots' => \App\Models\Appointment::TIME_SLOTS,
        ]);
    }

    public function qrScanner(): View
    {
        return view('Offices.OSAS.qr', [
            'office' => 'OSAS',
            'checkIns' => \App\Support\CheckInList::today('OSAS'),
        ]);
    }

    public function consultations(): View
    {
        return view('Offices.OSAS.consultations', [
            'office' => 'OSAS',
            'services' => ConsultationService::forOffice('OSAS')->ordered()->get(),
        ]);
    }

    public function profile(): View
    {
        return view('Offices.OSAS.profile', [
            'user' => auth()->user(),
            'office' => 'OSAS',
        ]);
    }

    public function help(): View
    {
        return view('Offices.OSAS.help', [
            'office' => 'OSAS',
        ]);
    }
}