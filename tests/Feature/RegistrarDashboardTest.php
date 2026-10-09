<?php

namespace Tests\Feature;

use App\Enums\DocumentRequestStatus;
use App\Models\Document;
use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrarDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function makeRegistrar(): User
    {
        return User::factory()->create(['role' => 'registrar']);
    }

    private function makeStudent(string $name): User
    {
        return User::factory()->create(['role' => 'student', 'name' => $name]);
    }

    private function makeRequest(User $student, string $status): DocumentRequest
    {
        $document = Document::create([
            'name' => 'Transcript of Records',
            'description' => 'TOR',
            'fee' => 100.00,
        ]);

        $request = $student->documentRequests()->create([
            'student_name' => $student->name,
            'student_address' => 'Iloilo City',
            'student_contact' => '09170000000',
            'student_course_year' => 'BSIT 3',
            'status' => $status,
            'purpose_type' => 'employment',
            'educational_status' => 'not_graduated',
            'educational_level' => 'college',
            'claim_mode' => 'personal',
            'submitted_at' => now(),
        ]);

        $request->documents()->attach($document->id);

        return $request;
    }

    /**
     * Each stat card prints its number in a <p> that is immediately followed
     * by the label's <p>, so the two can be matched as a pair rather than
     * counting stray digits anywhere on the page.
     */
    private function assertStatShows(string $html, string $label, int $expected): void
    {
        $pattern = '/>\s*' . preg_quote((string) $expected, '/') . '\s*<\/p>\s*<p[^>]*>\s*'
            . preg_quote($label, '/') . '\s*<\/p>/s';

        $this->assertMatchesRegularExpression(
            $pattern,
            $html,
            "Expected the \"{$label}\" card to show {$expected}."
        );
    }

    public function test_the_four_stat_cards_read_real_counts_from_the_database(): void
    {
        // One still waiting for the registrar.
        $this->makeRequest(
            $this->makeStudent('Cruz, Pedro'),
            DocumentRequestStatus::SUBMITTED->value
        );

        // Two awaiting signature.
        $this->makeRequest(
            $this->makeStudent('Santos, Maria Clara'),
            DocumentRequestStatus::FOR_SIGNATURE->value
        );
        $this->makeRequest(
            $this->makeStudent('Reyes, Jose'),
            DocumentRequestStatus::FOR_SIGNATURE->value
        );

        // Three claimed — but only across two distinct students, so the
        // served counter must count people, not requests.
        $servedA = $this->makeStudent('Dela Cruz, Ana');
        $servedB = $this->makeStudent('Garcia, Ben');
        $this->makeRequest($servedA, DocumentRequestStatus::COMPLETED->value);
        $this->makeRequest($servedA, DocumentRequestStatus::COMPLETED->value);
        $this->makeRequest($servedB, DocumentRequestStatus::COMPLETED->value);

        $html = $this->actingAs($this->makeRegistrar())
            ->get('/dashboard')
            ->assertOk()
            ->getContent();

        $this->assertStatShows($html, 'Pending Requests', 1);
        $this->assertStatShows($html, 'Approved Requests', 2);
        $this->assertStatShows($html, 'Issued Documents', 3);
        $this->assertStatShows($html, 'Students Served', 2);
    }

    public function test_the_stat_cards_link_to_their_matching_request_lists(): void
    {
        $this->actingAs($this->makeRegistrar())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee(route('registrar.document-requests.index', ['tab' => 'active', 'status' => 'submitted']))
            ->assertSee(route('registrar.document-requests.index', ['tab' => 'active', 'status' => 'for_signature']))
            ->assertSee(route('registrar.document-requests.index', ['tab' => 'archived', 'status' => 'completed']));
    }

    public function test_the_pending_table_names_the_students_behind_the_count(): void
    {
        $pending = $this->makeRequest(
            $this->makeStudent('Delacruz, Juan Miguel'),
            DocumentRequestStatus::SUBMITTED->value
        );

        // Neither of these is pending, so neither belongs in the table.
        $approved = $this->makeRequest(
            $this->makeStudent('Santos, Maria Clara'),
            DocumentRequestStatus::FOR_SIGNATURE->value
        );
        $issued = $this->makeRequest(
            $this->makeStudent('Reyes, Jose'),
            DocumentRequestStatus::COMPLETED->value
        );

        $this->actingAs($this->makeRegistrar())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee($pending->student_name)
            ->assertSee($pending->request_number)
            ->assertDontSee($approved->request_number)
            ->assertDontSee($issued->request_number);
    }

    public function test_the_pending_row_links_to_that_requests_details_page(): void
    {
        $pending = $this->makeRequest(
            $this->makeStudent('Delacruz, Juan Miguel'),
            DocumentRequestStatus::SUBMITTED->value
        );

        $this->actingAs($this->makeRegistrar())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee(route('registrar.document-requests.show', $pending), false);
    }

    public function test_the_dashboard_says_so_when_nothing_is_pending(): void
    {
        $this->makeRequest(
            $this->makeStudent('Santos, Maria Clara'),
            DocumentRequestStatus::COMPLETED->value
        );

        $this->actingAs($this->makeRegistrar())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('No Pending Requests');
    }
}
