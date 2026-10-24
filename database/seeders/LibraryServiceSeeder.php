<?php

namespace Database\Seeders;

use App\Models\LibraryService;
use Illuminate\Database\Seeder;

/**
 * The library's auxiliary services — the catalogue behind the Other Services
 * page.
 *
 * Uses firstOrCreate, not updateOrCreate: this seeds defaults, so re-seeding
 * fills in missing rows without reverting wording, sort order, or Active flags
 * the library has since edited.
 */
class LibraryServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'name' => 'Interactive Zone',
                'slug' => 'interactive-zone',
                'category' => 'access',
                'icon' => 'computer',
                'summary' => 'Internet-connected computers for research and online databases.',
                'description' => 'A cluster of internet-connected workstations inside the library, reserved for research, online journals and academic databases. Use is on a first-come, first-served basis and is meant for academic work during peak hours.',
                'how_to_avail' => 'Present your school ID at the circulation counter and sign the workstation logbook. Ask the librarian on duty for help signing in to the online databases.',
                'requirements' => "School ID\nAcademic purpose",
                'is_requestable' => false,
            ],
            [
                'name' => 'Current Awareness Service',
                'slug' => 'current-awareness',
                'category' => 'awareness',
                'icon' => 'megaphone',
                'summary' => 'Monthly acquisition lists and a bulletin board for new material.',
                'description' => 'Every month the library posts a list of newly acquired books, periodicals and non-print materials. Deans and faculty receive the list directly, and the bulletin board beside the circulation desk carries announcements about new titles, trials and schedules.',
                'how_to_avail' => 'Ask the librarian to be added to the monthly acquisition list, or read the bulletin board beside the circulation desk. Faculty and deans are added automatically.',
                'requirements' => "School ID or faculty status\nEmail address for the monthly list",
                'is_requestable' => true,
            ],
            [
                'name' => 'Referral Service',
                'slug' => 'referral-service',
                'category' => 'access',
                'icon' => 'letter',
                'summary' => 'Referral letters for libraries outside the university.',
                'description' => 'When a title you need is not held here, the library issues a referral letter so you can be admitted to another institution. The letter names the materials you need and the period it covers, and must be presented at the receiving library.',
                'how_to_avail' => 'Tell the librarian which titles you need and where they are held. The letter is prepared within one working day and released at the circulation counter.',
                'requirements' => "School ID\nTitle and author of the material\nName of the library to be visited",
                'is_requestable' => true,
            ],
            [
                'name' => 'Multimedia Service',
                'slug' => 'multimedia-service',
                'category' => 'access',
                'icon' => 'play',
                'summary' => 'CDs, DVDs, VHS tapes, slides and the equipment to play them.',
                'description' => 'Non-print holdings are kept at the multimedia counter and viewed in the library viewing area. Players, monitors and projectors are issued together with the material; equipment is for in-library use and is returned to the counter afterwards.',
                'how_to_avail' => 'Request the title at the multimedia counter and state the equipment you need. Materials are issued for use inside the library only.',
                'requirements' => "School ID\nTitle of the material\nEquipment needed",
                'is_requestable' => true,
            ],
            [
                'name' => 'Resource Sharing',
                'slug' => 'resource-sharing',
                'category' => 'access',
                'icon' => 'users',
                'summary' => 'Partner-library access under reciprocal agreements.',
                'description' => 'Under reciprocal agreements, students and faculty may use the collections of partner institutions. Access is granted on presentation of a signed referral letter, and the borrowing rules of the host library apply while you are there.',
                'how_to_avail' => 'Ask the librarian whether the title is available at a partner library, then request a signed referral letter before your visit.',
                'requirements' => "School ID\nSigned referral letter\nTitle of the material",
                'is_requestable' => true,
            ],
            [
                'name' => 'Photocopying Service',
                'slug' => 'photocopying-service',
                'category' => 'reproduction',
                'icon' => 'copy',
                'summary' => 'On-site reproduction of pages from library materials.',
                'description' => 'A photocopying facility is available for pages taken from library holdings, within copyright limits. Library staff operate the machine; self-service copying is not permitted, and the material must be checked out for copying and returned afterwards.',
                'how_to_avail' => 'Bring the material to the circulation counter and point out the pages needed. Copying is paid for at the counter.',
                'requirements' => "School ID\nLibrary material\nCopying fee",
                'is_requestable' => false,
            ],
            [
                'name' => 'Indexing Service',
                'slug' => 'indexing-service',
                'category' => 'research',
                'icon' => 'list',
                'summary' => 'Topic indexes drawn from journals, magazines and newspapers.',
                'description' => 'The library keeps subject indexes to articles in its journal, magazine and newspaper holdings, including open sources. A topic index tells you where an article appeared, so the issue can be requested directly instead of searching shelf by shelf.',
                'how_to_avail' => 'Give the librarian your topic and the period you want covered. The index is prepared from the print and open-source holdings and sent to you.',
                'requirements' => "Research topic\nPeriod covered\nSchool ID",
                'is_requestable' => true,
            ],
            [
                'name' => 'Scan and Deliver Service',
                'slug' => 'scan-and-deliver',
                'category' => 'reproduction',
                'icon' => 'scan',
                'summary' => 'Requested pages scanned and sent by email or Messenger.',
                'description' => 'Instead of visiting the library, request a scan of specific pages. Within copyright limits, the library scans the pages you name and sends the file to your email or Messenger. Longer requests take a little more time to prepare.',
                'how_to_avail' => 'Send the title, author and page numbers, together with your email address or Messenger name. Scans are sent within one to two working days.',
                'requirements' => "Title, author and page numbers\nEmail address or Messenger name",
                'is_requestable' => true,
            ],
            [
                'name' => 'Bibliographic Listing Service',
                'slug' => 'bibliographic-listing',
                'category' => 'research',
                'icon' => 'book',
                'summary' => 'Specialised material lists prepared for faculty members.',
                'description' => 'Faculty members may ask for a bibliographic listing on a subject or for a course: a formatted list of the library holdings, with call numbers, ready to be used in a syllabus or a reading list.',
                'how_to_avail' => 'Send the subject or course and the level of the students. Faculty requests are prioritised, and the list is delivered as a document.',
                'requirements' => "Subject or course\nFaculty status",
                'is_requestable' => true,
            ],
        ];

        foreach ($services as $index => $service) {
            LibraryService::firstOrCreate(
                ['slug' => $service['slug']],
                [
                    ...$service,
                    'is_active' => true,
                    'sort_order' => $index,
                ],
            );
        }

        $this->command?->info('Library services seeded: '.count($services).'.');
    }
}
