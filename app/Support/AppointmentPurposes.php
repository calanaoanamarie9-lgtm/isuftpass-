<?php

namespace App\Support;

use App\Enums\Office;
use App\Models\LibraryService;

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
     * The full office => [purpose value => label] map that drives the booking
     * form's dynamic Purpose dropdown, with the "Other" option appended to
     * every office.
     *
     * @param  list<string>  $offices
     * @return array<string, array<string, string>>
     */
    public static function toSelectMap(array $offices): array
    {
        $map = [];

        foreach ($offices as $office) {
            $purposes = [];

            foreach (self::forOffice($office) as $purpose) {
                $purposes[$purpose] = $purpose;
            }

            $purposes[self::OTHER] = 'Other';

            $map[$office] = $purposes;
        }

        return $map;
    }
}
