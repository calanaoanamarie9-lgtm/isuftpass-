<?php

namespace App\Http\Controllers\Consultation;

use App\Http\Controllers\Controller;
use App\Models\ConsultationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Consultation services owned by the signed-in office / department account.
 *
 * Ownership is derived from auth()->user()->office — never from the URL — so a
 * department account that is allowed through the route middleware still cannot
 * create or edit another office's services. The per-service decisions live in
 * ConsultationServicePolicy (which also grants the System Admin master access).
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
            ->route($this->prefix().'.consultations')
            ->with('status', "“{$consultationService->name}” was added to your consultation services.");
    }

    public function edit(ConsultationService $consultationService): View
    {
        Gate::authorize('update', $consultationService);

        return view('consultations.form', [
            'office' => $this->office(),
            'service' => $consultationService,
        ]);
    }

    public function update(Request $request, ConsultationService $consultationService): RedirectResponse
    {
        Gate::authorize('update', $consultationService);

        $consultationService->update($this->validated($request));

        return back()->with('status', 'Consultation service updated.');
    }

    public function destroy(ConsultationService $consultationService): RedirectResponse
    {
        Gate::authorize('delete', $consultationService);

        $consultationService->delete();

        return back()->with('status', 'Consultation service removed.');
    }

    /**
     * Quick activate / deactivate from the office's service list.
     *
     * Deactivating keeps the service (and its history) intact — it simply
     * disappears from the student catalogue until it is reactivated, so an
     * office can pull a service without deleting it.
     */
    public function toggle(ConsultationService $consultationService): RedirectResponse
    {
        Gate::authorize('toggle', $consultationService);

        $consultationService->update([
            'is_active' => ! $consultationService->is_active,
        ]);

        return back()->with(
            'status',
            $consultationService->is_active
                ? "“{$consultationService->name}” is active again — students can see it."
                : "“{$consultationService->name}” is now hidden from students."
        );
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
}
