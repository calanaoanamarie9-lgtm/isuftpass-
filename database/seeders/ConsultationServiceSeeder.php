<?php

namespace Database\Seeders;

use App\Models\ConsultationService;
use Illuminate\Database\Seeder;

/**
 * Carries the previously hard-coded consultation services into the database
 * so every office / department can manage its own list.
 *
 * Uses updateOrCreate, so re-seeding never duplicates or deletes rows.
 */
class ConsultationServiceSeeder extends Seeder
{
    public function run(): void
    {
        $offices = [
            'Accounting' => [
                ['Student Account & Balance', 'Consultation regarding student account balances, charges, outstanding fees, and payment records.'],
                ['Assessment & School Fees', 'Get assistance regarding assessment details, tuition, miscellaneous fees, and other school-related charges.'],
                ['Payment Concerns', 'Assistance with payment concerns, payment verification, transaction records, and related accounting inquiries.'],
                ['Refund & Adjustment Inquiry', 'Consultation regarding possible refunds, account adjustments, overpayments, and related financial concerns.'],
                ['Official Receipt & Payment Records', 'Assistance with payment records, official receipts, and verification of financial transactions.'],
                ['General Accounting Consultation', 'For other student financial concerns that require assistance from the Accounting Office.'],
            ],
            'Guidance' => [
                ['Personal Counseling', 'Schedule a private consultation for personal concerns, emotions, adjustment, or other student-related matters.'],
                ['Academic Counseling', 'Consultation regarding academic concerns, study difficulties, and student academic adjustment.'],
                ['Career Guidance', 'Get guidance related to career planning, career interests, employment preparation, and future career decisions.'],
                ['Course & Study Planning', 'Assistance with study planning, academic direction, and concerns related to student educational goals.'],
                ['Wellness Consultation', 'Request a consultation for student wellness, adjustment, personal development, and well-being concerns.'],
                ['General Guidance Consultation', 'For other student concerns that require assistance or consultation from the Guidance Office.'],
            ],
            'Library' => [
                ['Library Orientation', 'Get introduced to library facilities, collections, services, rules, and available resources.'],
                ['Research Assistance', 'Get assistance in finding books, journals, articles, and other academic research materials.'],
                ['Reference Assistance', 'Ask for help locating reliable reference materials and appropriate information sources.'],
                ['Thesis / Capstone Research', 'Consultation for students conducting thesis, capstone, or academic research.'],
                ['Online Database Assistance', 'Assistance with accessing online journals, academic databases, and electronic resources.'],
                ['General Library Consultation', 'For other library-related concerns, questions, and services that require staff assistance.'],
            ],
            'OSAS' => [
                ['Student Affairs', 'Consultation regarding student affairs, concerns, and available student services.'],
                ['Student Services', 'Get assistance with student-related services, programs, and concerns.'],
                ['Scholarship & Assistance', 'Ask about scholarships, student assistance, and available support programs.'],
                ['Student Organizations', 'Consultation about student organizations, activities, and organizational concerns.'],
                ['Other Concerns', 'For other student concerns that require consultation with the OSAS Office.'],
                ['General Consultation', 'Book a consultation for a concern that does not fall under the listed services.'],
            ],
            'CICI' => $this->departmentTypes(),
            'CBMSD' => $this->departmentTypes(),
            'COAG' => $this->departmentTypes(),
            'COED' => $this->departmentTypes(),
        ];

        foreach ($offices as $office => $services) {
            foreach ($services as $index => [$name, $description]) {
                ConsultationService::updateOrCreate(
                    ['office' => $office, 'name' => $name],
                    [
                        'description' => $description,
                        'is_active' => true,
                        'sort_order' => $index,
                    ],
                );
            }
        }

        $this->command?->info('Consultation services seeded for ' . count($offices) . ' offices / departments.');
    }

    /**
     * Shared appointment types offered by every college department.
     *
     * @return array<int, array{0: string, 1: string}>
     */
    private function departmentTypes(): array
    {
        return [
            ['Capstone Consultation', null],
            ['OJT Advising', null],
            ['Academic Advising', null],
            ['Research Guidance', null],
            ['General Inquiry', null],
        ];
    }
}
