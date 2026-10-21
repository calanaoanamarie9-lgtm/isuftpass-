<?php

namespace App\Support;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * The data behind the appointments list, for whoever is asking.
 *
 * The registrar sees every office; every other staff account sees its own.
 * The list is the same either way - the same cards, the same filters, the
 * same record behind "View Details" - so it is built once here.
 */
class AppointmentList
{
    /**
     * The list, and the filters that shaped it.
     *
     * @return array{appointments: LengthAwarePaginator, statusFilter: string|null, userTypeFilter: string|null}
     */
    public static function for(User $user, Request $request): array
    {
        Gate::authorize('viewAny', Appointment::class);

        // The filter speaks the office's language: "Approved" is the label
        // the page prints for a stored "confirmed" status, so the option is
        // turned back into the value the column actually holds.
        $statusFilter = $request->query('status');
        $statusFilter = is_string($statusFilter) ? $statusFilter : null;

        $userTypeFilter = $request->query('user_type');
        $userTypeFilter = is_string($userTypeFilter) ? $userTypeFilter : null;

        $status = [
            'pending' => AppointmentStatus::PENDING->value,
            'approved' => AppointmentStatus::CONFIRMED->value,
            'rescheduled' => AppointmentStatus::RESCHEDULED->value,
            'cancelled' => AppointmentStatus::CANCELLED->value,
        ][$statusFilter] ?? null;

        // The System Admin answers for every office, so their appointment
        // list is not narrowed to one - the list names the office on each
        // row. A registrar or an office account still sees only its own.
        $appointmentsQuery = Appointment::with('user')
            ->when(
                $user->role !== 'admin',
                fn ($query) => $query->where('office', $user->officeScope())
            )
            ->when($status, fn ($query) => $query->where('status', $status));

        // Applicant classification filter. "Student" is every account that
        // signed up as a student (registration_type is left null for them);
        // alumni / parent / guest were typed into registration_type at signup.
        $userTypes = [
            'student' => 'Student',
            'alumni' => 'Alumni',
            'parent' => 'Parent',
            'guest' => 'Guest',
        ];

        $userTypeFilter = is_string($userTypeFilter) && array_key_exists($userTypeFilter, $userTypes)
            ? $userTypeFilter
            : null;

        if ($userTypeFilter === 'student') {
            $appointmentsQuery->whereHas('user', fn ($q) => $q->whereNull('registration_type'));
        } elseif ($userTypeFilter !== null) {
            // "Parent" also covers the legacy 'guardian' registration_type.
            $registrations = $userTypeFilter === 'parent' ? ['parent', 'guardian'] : [$userTypeFilter];
            $appointmentsQuery->whereHas('user', fn ($q) => $q->whereIn('registration_type', $registrations));
        }

        return [
            'appointments' => $appointmentsQuery->firstComeFirstServed()->paginate(12)->withQueryString(),
            'statusFilter' => $statusFilter,
            'userTypeFilter' => $userTypeFilter,
        ];
    }
}
