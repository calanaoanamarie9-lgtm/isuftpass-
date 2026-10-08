<?php

namespace App\Http\Controllers\Consultation;

use App\Http\Controllers\Controller;
use App\Models\ConsultationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Consultation services owned by the signed-in office / department account.
 *
 * Ownership is derived from auth()->user()->office — never from the URL — so a
 * department account that is allowed through the route middleware still cannot
 * create or edit another office's services.
 */
class ConsultationServiceController extends Controller
{
    public function create(): View
    {
        return view('consultations.form', [
            'office' => $this->office(),
            'service' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $office = $this->office();

        $consultationService = ConsultationService::create([
            ...$this->validated($request),
            'office' => $office,
            'sort_order' => (int) ConsultationService::forOffice($office)->max('sort_order') + 1,
        ]);

        return redirect()
            ->route($this->prefix() . '.consultations')
            ->with('status', "“{$consultationService->name}” was added to your consultation services.");
    }

    public function edit(ConsultationService $consultationService): View
    {
        $this->authorizeOwnership($consultationService);

        return view('consultations.form', [
            'office' => $this->office(),
            'service' => $consultationService,
        ]);
    }

    public function update(Request $request, ConsultationService $consultationService): RedirectResponse
    {
        $this->authorizeOwnership($consultationService);

        $consultationService->update($this->validated($request));

        return back()->with('status', 'Consultation service updated.');
    }

    public function destroy(ConsultationService $consultationService): RedirectResponse
    {
        $this->authorizeOwnership($consultationService);

        $consultationService->delete();

        return back()->with('status', 'Consultation service removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function office(): string
    {
        // Any staff account manages its own office's services — including
        // self-registered offices, whose names have no enum case and no
        // place in a hardcoded whitelist. Ownership checks below keep one
        // office out of another office's rows.
        return auth()->user()->officeScope();
    }

    /**
     * Route name prefix of the group the request came in on — derived from
     * the route name, not the office name, because a self-registered office
     * (e.g. "Clinic") has no route group of its own and works from the
     * shared 'workspace.' group instead.
     */
    private function prefix(): string
    {
        return (string) preg_replace(
            '/\.consultations(\..*)?$/',
            '',
            (string) request()->route()?->getName()
        );
    }

    private function authorizeOwnership(ConsultationService $consultationService): void
    {
        abort_unless(
            $consultationService->office === $this->office(),
            403,
            'This consultation service belongs to another office.',
        );
    }
}
