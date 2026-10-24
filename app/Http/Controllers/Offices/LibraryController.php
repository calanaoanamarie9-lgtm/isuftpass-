<?php

namespace App\Http\Controllers\Offices;

use App\Http\Controllers\Controller;
use App\Models\ConsultationService;
use App\Models\LibraryService;
use App\Support\AppointmentList;
use App\Support\AvailabilityPage;
use App\Support\CheckInList;
use App\Support\WorkspaceDashboard;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LibraryController extends Controller
{
    public function dashboard(): View
    {
        return view('Offices.Library.dashboard', WorkspaceDashboard::data('Library'));
    }

    public function appointments(Request $request): View
    {
        return view('registrar.appointments.index', [
            ...AppointmentList::for(request()->user(), $request),
            'prefix' => 'library',
        ]);
    }

    public function availability(Request $request): View
    {
        return view('registrar.availability', [
            ...AvailabilityPage::for('Library', $request->input('month')),
            'prefix' => 'library',
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

    /**
     * The auxiliary services — computers, referrals, scans and so on.
     *
     * The cards are rendered on the server so the page works without
     * JavaScript; $cards feeds the search, the category chips and the request
     * panel, so both read the same catalogue.
     */
    public function services(): View
    {
        $services = LibraryService::query()->active()->ordered()->get();

        return view('Offices.Library.services', [
            'office' => 'Library',
            'services' => $services,
            'cards' => $services->map->toCard()->values(),
            'categories' => LibraryService::CATEGORIES,
        ]);
    }
}
