<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ConsultationService;
use App\Support\CheckInList;
use App\Support\TimeSlots;
use App\Support\WorkspaceDashboard;
use Illuminate\View\View;

/**
 * Workspace for self-registered offices — an office account whose typed
 * office name is none of the eight built-in workspaces (OSAS, Accounting,
 * Library, Guidance, CICI, CBMSD, COAG, COED) lands here after admin
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

    public function appointments(): View
    {
        $office = auth()->user()->officeScope();

        return view('workspace.appointments', [
            'office' => $office,
            'appointments' => Appointment::with('user')
                ->where('office', $office)
                ->firstComeFirstServed()
                ->paginate(12),
            'workspacePrefix' => 'workspace',
        ]);
    }

    public function availability(): View
    {
        $office = auth()->user()->officeScope();

        return view('workspace.availability', [
            'office' => $office,
            'timeSlots' => TimeSlots::forOffice($office),
            'workspacePrefix' => 'workspace',
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

    public function profile(): View
    {
        return view('workspace.profile', [
            'user' => auth()->user(),
            'office' => auth()->user()->officeScope(),
        ]);
    }

    public function help(): View
    {
        return view('workspace.help', [
            'office' => auth()->user()->officeScope(),
        ]);
    }
}
