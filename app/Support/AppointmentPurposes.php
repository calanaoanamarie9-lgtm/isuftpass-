<?php

namespace App\Support;

use App\Enums\Office;
use App\Models\ConsultationService;
use App\Models\LibraryService;
use App\Models\User;

/**
 * Curated appointment purposes offered in the booking form's dynamic Purpose
 * dropdown, one list per office. Booking the Registrar offers its standard
 * documents / services; every other office offers its own set.
 *
 * "Other" is always appended with a sentinel value so the form and controller
 * can tell a chosen preset apart from a purpose the student typed themselves.
 */
class AppointmentPurposes
{
    /** Value stored in the Purpose dropdown for the free-text "Other" option. */
    public const OTHER = '__other__';

    /**
     * Purposes offered by one office / department.
     *
     * @return list<string>
     */
    public static function forOffice(?string $office): array
    {
        // Every college department books the same consultation types.
        $departments = [
            'Capstone Consultation',
            'OJT Advising',
            'Academic Advising',
            'Research Guidance',
            'General Inquiry',
        ];

        return match ($office) {
            Office::Registrar->value => [
                'Transcript of Records',
                'Diploma',
                'Certificate of Grades',
                'Good Moral Certificate',
                'Form 137 / SF10',
                'Honorable Dismissal',
                'Authentication / CAV',
                'Certificate of Enrollment',
                'Certificate of Registration',
                'Transfer Credentials',
            ],
            Office::SSC->value => [
                'Student Affairs & Welfare Concern',
                'Scholarship & Financial Assistance',
                'Student Organizations & Activities',
                'Counseling & Psychological Support',
                'Student Discipline & Clearance',
                'Grievance / Complaint Assistance',
                'General Inquiry',
            ],
            Office::Cashier->value => [
                'Payment of School Fees',
                'Issuance of Official Receipt',
                'Assessment & Statement of Account',
                'Refund & Adjustment Inquiry',
                'Outstanding Balance Inquiry',
                'General Cashier Concern',
            ],
            Office::Accounting->value => [
                'Student Account & Balance',
                'Assessment & School Fees',
                'Payment Concerns',
                'Refund & Adjustment Inquiry',
                'Official Receipt & Payment Records',
                'General Accounting Consultation',
            ],
            Office::Library->value => array_merge(
                [
                    'Library Orientation',
                    'Research Assistance',
                    'Reference Assistance',
                    'Thesis / Capstone Consultation',
                    'Online Database Assistance',
                    'Borrowing & Returning of Materials',
                    'General Library Consultation',
                ],
                LibraryService::active()->ordered()->pluck('name', 'name')->toArray(),
            ),
            Office::Cici->value,
            Office::CBMSD->value,
            Office::Coag->value,
            Office::Coed->value => $departments,
            default => [
                'General Consultation / Concern',
                'Document / Certification Request',
                'General Inquiry',
            ],
        };
    }

    /**
     * Offices that manage their own consultation services from a panel.
     *
     * For these offices the panel is the single source of truth for the
     * booking form's Purpose dropdown: no rows means no presets, so the
     * dropdown offers "Other" alone — the curated baseline below is never
     * resurrected for them. A self-registered office account manages its
     * rows from the shared 'workspace' panel, so it counts as well.
     *
     * Offices without a panel (the Registrar's document counter, the Cashier)
     * keep answering from the curated list.
     */
    public static function managesServices(?string $office): bool
    {
        if ($office === null || $office === '') {
            return false;
        }

        // Registrar now manages its own services from its panel:
        // 0 services -> only Other; has services -> those services.
        if ($office === Office::Registrar->value) {
            return true;
        }

        $panelOffices = [
            Office::Cici->value,
            Office::CBMSD->value,
            Office::Coag->value,
            Office::Coed->value,
            Office::SSC->value,
            Office::Accounting->value,
            Office::Library->value,
        ];

        if (in_array($office, $panelOffices, true)) {
            return true;
        }

        // The remaining built-in offices (the Registrar, the Cashier) keep
        // their curated list even when an office account exists for the name —
        // a Registrar account's rows, when it adds any, still win above.
        if (in_array($office, array_column(Office::cases(), 'value'), true)) {
            return false;
        }

        return User::query()
            ->where('role', User::ROLE_OFFICE)
            ->where('office', $office)
            ->where('approval_status', User::APPROVAL_APPROVED)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * The office's active consultation services, as {id, name} pairs — the
     * live source the booking form's Purpose dropdown is built from.
     *
     * The database wins whenever the office keeps rows of its own: a service
     * its panel adds, renames, deactivates or deletes changes this list on
     * the next request, with no hardcoded edit and no deploy — deactivating
     * the last one leaves only "Other" rather than resurrecting the built-in
     * baseline. A panel office that has never had a row answers with an empty
     * list for the same reason: an empty panel means the purpose dropdown
     * offers only "Other". Offices without a panel (the Registrar, the
     * Cashier) fall back to the curated list above, so their dropdown never
     * comes up empty.
     *
     * @return list<array{id: int|null, name: string}>
     */
    public static function activeServices(?string $office): array
    {
        if ($office !== null && $office !== '') {
            $hasRows = ConsultationService::query()->forOffice($office)->exists();

            if ($hasRows || self::managesServices($office)) {
                return ConsultationService::query()
                    ->forOffice($office)
                    ->active()
                    ->ordered()
                    ->get(['id', 'name'])
                    ->map(fn (ConsultationService $service) => ['id' => $service->id, 'name' => $service->name])
                    ->all();
            }
        }

        return array_map(
            fn (string $name) => ['id' => null, 'name' => $name],
            self::forOffice($office ?: null),
        );
    }

    /**
     * The full office => [purpose value => label] map the booking form uses,
     * built live from every office's active services and closed with "Other".
     *
     * Values are the purpose names themselves, so a choice posts the label the
     * office will read back on the appointment — not a positional index.
     *
     * @param  list<string>  $offices
     * @return array<string, array<string, string>>
     */
    public static function toActiveSelectMap(array $offices): array
    {
        $map = [];

        foreach ($offices as $office) {
            $purposes = [];

            foreach (self::activeServices($office) as $service) {
                $purposes[$service['name']] = $service['name'];
            }

            $purposes[self::OTHER] = 'Other';

            $map[$office] = $purposes;
        }

        return $map;
    }
}
