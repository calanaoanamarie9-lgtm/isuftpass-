<?php

namespace App\Http\Controllers\Offices;

use App\Http\Controllers\Controller;
use App\Models\ConsultationService;
use Illuminate\View\View;

class LibraryController extends Controller
{
    public function dashboard(): View
    {
        return view('Offices.Library.dashboard', \App\Support\WorkspaceDashboard::data('Library'));
    }

    public function appointments(): View
    {
        return view('Offices.Library.appointments', [
            'office' => 'Library',
            'appointments' => \App\Models\Appointment::with('user')
                ->where('office', 'Library')
                ->latest()
                ->paginate(12),
        ]);
    }

    public function availability(): View
    {
        return view('Offices.Library.availability', [
            'office' => 'Library',
            'timeSlots' => \App\Models\Appointment::TIME_SLOTS,
        ]);
    }

    public function qrScanner(): View
    {
        return view('Offices.Library.qr', [
            'office' => 'Library',
            'checkIns' => \App\Support\CheckInList::today('Library'),
        ]);
    }

    public function consultations(): View
    {
        return view('Offices.Library.consultations', [
            'office' => 'Library',
            'services' => ConsultationService::forOffice('Library')->ordered()->get(),
        ]);
    }

    public function profile(): View
    {
        return view('Offices.Library.profile', [
            'user' => auth()->user(),
            'office' => 'Library',
        ]);
    }

    public function help(): View
    {
        return view('Offices.Library.help', [
            'office' => 'Library',
        ]);
    }
}