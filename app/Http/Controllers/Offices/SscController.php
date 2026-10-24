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

class SscController extends Controller
{
    private const OFFICE = 'SSC';

    public function dashboard(): View
    {
        return view('Offices.SSC.dashboard', WorkspaceDashboard::data(self::OFFICE));
    }

    public function appointments(Request $request): View
    {
        return view('registrar.appointments.index', [
            ...AppointmentList::for(request()->user(), $request),
            'prefix' => 'ssc',
        ]);
    }

    public function availability(Request $request): View
    {
        return view('registrar.availability', [
            ...AvailabilityPage::for(self::OFFICE, $request->input('month')),
            'prefix' => 'ssc',
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
}
