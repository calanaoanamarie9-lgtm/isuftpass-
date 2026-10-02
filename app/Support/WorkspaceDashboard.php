<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\ConsultationService;
use Illuminate\Support\Collection;

/**
 * Shared numbers behind every office and department dashboard.
 *
 * All eight workspaces (Accounting, Guidance, Library, OSAS and the CICI,
 * CBMSD, COAG, COED departments) render the same tiles, so the queries live
 * here once instead of being copied into eight controllers.
 */
class WorkspaceDashboard
{
    /**
     * @return array{
     *     office: string,
     *     stats: array<string, int>,
     *     recent: Collection<int, Appointment>,
     *     services: Collection<int, ConsultationService>
     * }
     */
    public static function data(string $office): array
    {
        $scoped = Appointment::query()->where('office', $office);

        $services = ConsultationService::forOffice($office)->ordered()->get();

        return [
            'office' => $office,
            'stats' => [
                'total' => (clone $scoped)->count(),
                'pending' => (clone $scoped)->where('status', 'pending')->count(),
                'today' => (clone $scoped)->whereDate('date', now()->toDateString())->count(),
                'completed' => (clone $scoped)->where('status', 'completed')->count(),
                'services' => $services->count(),
            ],
            'recent' => Appointment::with('user')
                ->where('office', $office)
                ->latest()
                ->take(6)
                ->get(),
            'services' => $services,
        ];
    }
}
