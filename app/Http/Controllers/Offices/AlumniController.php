<?php

namespace App\Http\Controllers\Offices;

use App\Http\Controllers\Controller;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\DocumentRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AlumniController extends Controller
{
    /**
     * Alumni dashboard — mirrors web.php's alumni dispatch keys exactly.
     */
    public function dashboard(): View
    {
        $user = Auth::user();

        $recentActivity = collect()
            ->merge(
                $user->appointments()->latest()->limit(3)->get()->map(fn (Appointment $a) => [
                    'type' => 'appointment',
                    'title' => 'Visit with ' . $a->office,
                    'date' => $a->created_at,
                    'badge' => match ($a->status) {
                        'confirmed' => ['label' => 'Confirmed', 'class' => 'bg-green-50 text-green-700'],
                        'completed' => ['label' => 'Completed', 'class' => 'bg-blue-50 text-blue-700'],
                        'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-red-50 text-red-600'],
                        default => ['label' => 'Pending', 'class' => 'bg-yellow-50 text-yellow-700'],
                    },
                ])
            )
            ->merge(
                $user->documentRequests()->latest()->limit(3)->get()->map(fn (DocumentRequest $d) => [
                    'type' => 'document',
                    'title' => $d->documentsSummary(),
                    'date' => $d->created_at,
                    'badge' => ['label' => ucwords(str_replace('_', ' ', $d->status)), 'class' => 'bg-blue-50 text-blue-700'],
                ])
            )
            ->filter(fn ($activity) => $activity['date'])
            ->sortByDesc('date')
            ->take(5)
            ->values();

        return view('alumni.dashboard', [
            'activeDocumentRequests' => $user->documentRequests()->active()->count(),
            'upcomingAppointments' => $user->appointments()->upcoming()->count(),
            'completedTransactions' => $user->documentRequests()
                ->whereIn('status', ['completed'])
                ->count() + $user->appointments()->where('status', AppointmentStatus::COMPLETED->value)->count(),
            'hasPass' => (bool) ($user->studentProfile?->pass_token),
            'recentActivity' => $recentActivity,
            'bannerEnabled' => Setting::get('banner_enabled') === 'true',
            'bannerText' => Setting::get('banner_text'),
        ]);
    }

    /**
     * Alumni document requests (list).
     */
    public function documents(): View
    {
        $user = Auth::user();

        return view('alumni.documents', [
            'documents' => $user->documentRequests()->latest()->get(),
            'user' => $user,
        ]);
    }

    /**
     * Alumni upcoming appointments.
     */
    public function appointments(): View
    {
        $user = Auth::user();

        return view('alumni.appointments', [
            'appointments' => $user->appointments()->upcoming()->firstComeFirstServed()->get(),
            'user' => $user,
        ]);
    }

    /**
     * Alumni QR pass.
     */
    public function pass(): View
    {
        $user = Auth::user();

        return view('alumni.pass', [
            'qrCode' => $user->studentProfile?->pass_token
                ? 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode(route('alumni.pass.show', ['token' => $user->studentProfile->pass_token]))
                : null,
            'user' => $user,
        ]);
    }

    /**
     * Alumni profile settings.
     */
    public function profile(): View
    {
        return view('alumni.profile', ['user' => Auth::user()]);
    }

    /**
     * Alumni profile update (placeholder — persists nothing).
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        return redirect()
            ->route('alumni.profile.show')
            ->with('status', 'Profile updated successfully.');
    }

    /**
     * Alumni help & FAQs.
     */
    public function help(): View
    {
        return view('alumni.help', ['user' => Auth::user()]);
    }
}
