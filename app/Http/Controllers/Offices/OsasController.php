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
        ]);
    }

    public function availability(): View
    {
        return view('Offices.OSAS.availability', [
            'office' => 'OSAS',
        ]);
    }

    public function qrScanner(): View
    {
        return view('Offices.OSAS.qr', [
            'office' => 'OSAS',
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