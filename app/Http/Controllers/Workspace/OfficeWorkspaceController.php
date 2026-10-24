<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\ConsultationService;
use App\Support\AppointmentList;
use App\Support\AvailabilityPage;
use App\Support\CheckInList;
use App\Support\WorkspaceDashboard;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Workspace for self-registered offices — an office account whose typed
 * office name is none of the seven built-in workspaces (Accounting,
 * Library, SSC, CICI, CBMSD, COAG, COED) lands here after admin
 * approval and email verification.
 *
 * Appointments only: document requests stay with the Registrar. Everything
 * is scoped to auth()->user()->officeScope(), so one implementation serves
 * every office that applies.
 */
class OfficeWorkspaceController extends Controller
{
    public function dashboard(): View
    {
        return view('workspace.dashboard', [
            ...WorkspaceDashboard::data(auth()->user()->officeScope()),
            'workspacePrefix' => 'workspace',
        ]);
    }

    public function appointments(Request $request): View
    {
        return view('registrar.appointments.index', [
            ...AppointmentList::for(request()->user(), $request),
            'prefix' => 'workspace',
        ]);
    }

    public function availability(Request $request): View
    {
        return view('registrar.availability', [
            ...AvailabilityPage::for(auth()->user()->officeScope(), $request->input('month')),
            'prefix' => 'workspace',
        ]);
    }

    public function qrScanner(): View
    {
        $office = auth()->user()->officeScope();

        return view('workspace.qr', [
            'office' => $office,
            'checkIns' => CheckInList::today($office),
        ]);
    }

    public function consultations(): View
    {
        $office = auth()->user()->officeScope();

        return view('workspace.consultations', [
            'office' => $office,
            'services' => ConsultationService::forOffice($office)->ordered()->get(),
        ]);
    }
}
