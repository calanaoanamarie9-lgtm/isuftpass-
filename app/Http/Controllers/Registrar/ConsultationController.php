<?php

namespace App\Http\Controllers\Registrar;

use App\Http\Controllers\Controller;
use App\Models\ConsultationService;
use Illuminate\View\View;

/**
 * The Registrar's own consultation services — the presets students see in the
 * booking form's Purpose dropdown for this office.
 *
 * Management (add / edit / toggle / remove) is shared with every other office:
 * ConsultationServiceController scopes the rows by auth()->user()->officeScope(),
 * which is 'Registrar' for a registrar account with no office set.
 */
class ConsultationController extends Controller
{
    private const OFFICE = 'Registrar';

    public function index(): View
    {
        return view('Offices.Registrar.consultations', [
            'office' => self::OFFICE,
            'services' => ConsultationService::forOffice(self::OFFICE)->ordered()->get(),
        ]);
    }
}
