<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Feedback Management — what users said about a service once it finished.
 *
 * Two readings share the page. The numbers come first: how many ratings
 * there are, the overall average, the 5-to-1 distribution and each
 * transaction type on its own, so an admin can see at a glance which
 * service is dragging the score down. The words come second: every
 * comment, newest first, filterable by type and by stars, so the reason
 * behind a low average can be read rather than guessed at.
 */
class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $typeFilter = in_array($request->query('type'), array_keys($types = self::types()), true)
            ? $request->query('type')
            : null;

        $ratingFilter = filter_var(
            $request->query('rating'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 5]],
        ) ?: null;

        // The summary reads every row on purpose, filters and all: a
        // narrowed view must never masquerade as the system-wide score.
        $totals = Feedback::query()
            ->selectRaw('count(*) as total')
            ->selectRaw('avg(rating) as average')
            ->first();

        $total = (int) ($totals->total ?? 0);

        $distribution = array_fill(1, 5, 0);

        foreach (Feedback::query()
            ->select('rating')
            ->selectRaw('count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating') as $stars => $count
        ) {
            $distribution[(int) $stars] = (int) $count;
        }

        $byType = [];

        foreach (Feedback::query()
            ->select('transaction_type')
            ->selectRaw('count(*) as total')
            ->selectRaw('avg(rating) as average')
            ->groupBy('transaction_type')
            ->get() as $row
        ) {
            $byType[$row->transaction_type] = [
                'total' => (int) $row->total,
                'average' => round((float) $row->average, 1),
            ];
        }

        $query = Feedback::query()->with(['user', 'feedbackable'])->latest('id');

        if ($typeFilter !== null) {
            $query->where('transaction_type', $typeFilter);
        }

        if ($ratingFilter !== null) {
            $query->where('rating', $ratingFilter);
        }

        return view('admin.feedback.index', [
            'feedbacks' => $query->paginate(15)->withQueryString(),
            'typeFilter' => $typeFilter,
            'ratingFilter' => $ratingFilter,
            'types' => $types,
            'total' => $total,
            'average' => $total > 0 ? round((float) $totals->average, 1) : null,
            'distribution' => $distribution,
            'byType' => $byType,
        ]);
    }

    /**
     * The two transactions a user may rate, keyed by the label the page
     * prints next to the filter.
     *
     * @return array<string, string>
     */
    private static function types(): array
    {
        return [
            Feedback::TYPE_APPOINTMENT => 'Appointments',
            Feedback::TYPE_DOCUMENT_REQUEST => 'Document Requests',
        ];
    }
}
