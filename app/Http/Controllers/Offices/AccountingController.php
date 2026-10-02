<?php

namespace App\Http\Controllers\Offices;

use App\Http\Controllers\Controller;
use App\Models\ConsultationService;
use Illuminate\View\View;

class AccountingController extends Controller
{
    public function dashboard(): View
    {
        return view('Offices.Accounting.dashboard', \App\Support\WorkspaceDashboard::data('Accounting'));
    }

    public function appointments(): View
    {
        return view('Offices.Accounting.appointments', [
            'office' => 'Accounting',
        ]);
    }

    public function availability(): View
    {
        return view('Offices.Accounting.availability', [
            'office' => 'Accounting',
        ]);
    }

    public function qrScanner(): View
    {
        return view('Offices.Accounting.qr', [
            'office' => 'Accounting',
        ]);
    }

    public function consultations(): View
    {
        return view('Offices.Accounting.consultations', [
            'office' => 'Accounting',
            'services' => ConsultationService::forOffice('Accounting')->ordered()->get(),
        ]);
    }

    public function profile(): View
    {
        return view('Offices.Accounting.profile', [
            'user' => auth()->user(),
            'office' => 'Accounting',
        ]);
    }

    public function help(): View
    {
        return view('Offices.Accounting.help', [
            'office' => 'Accounting',
        ]);
    }
}