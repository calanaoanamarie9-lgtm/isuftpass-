<?php

namespace App\Http\Controllers\Student;

use App\Enums\Office;
use App\Http\Controllers\Controller;
use App\Models\ConsultationService;
use Illuminate\View\View;

/**
 * Public catalogue of every consultation service offered across the
 * university — students pick a service and are taken to the appointment
 * booking form with the office and purpose already filled in.
 */
class ConsultationCatalogController extends Controller
{
    public function index(): View
    {
        $services = ConsultationService::query()
            ->active()
            ->ordered()
            // Only services of offices a student can actually book: a card
            // that preselects an unbookable office would dead-end on the
            // booking form's own list.
            ->whereIn('office', Office::bookableKeys())
            ->get()
            ->groupBy(fn (ConsultationService $service) => $service->office)
            ->filter()
            ->sortKeys();

        return view('student.consultations.index', [
            'services' => $services,
            'total' => $services->flatten()->count(),
            'labels' => collect(Office::cases())
                ->mapWithKeys(fn (Office $office) => [$office->value => $office->label()])
                ->all(),
        ]);
    }
}
