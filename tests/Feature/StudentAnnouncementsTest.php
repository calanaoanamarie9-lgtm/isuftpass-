<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The board the student dashboard links to.
 *
 * The controller and the dashboard card were both there, but the two views
 * were never written, so the card opened a 500. These tests hold the pages
 * that replaced them: what the board lists, what one announcement reads
 * like, and who is allowed to see it.
 */
class StudentAnnouncementsTest extends TestCase
{
    use RefreshDatabase;

    private function makeStudent(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    private function announcement(array $overrides = []): Announcement
    {
        return Announcement::create([
            'title' => 'Enrollment Week',
            'office' => 'Registrar',
            'body' => 'Enrollment runs from Monday to Friday at the Registrar.',
            'is_published' => true,
            'published_at' => now()->subHour(),
            ...$overrides,
        ]);
    }

    public function test_the_board_lists_only_what_is_live(): void
    {
        $live = $this->announcement();
        $draft = $this->announcement(['title' => 'Draft notice', 'is_published' => false]);
        $scheduled = $this->announcement(['title' => 'Next week', 'published_at' => now()->addWeek()]);

        $this->actingAs($this->makeStudent())
            ->get('/student/announcements')
            ->assertOk()
            ->assertSee('Announcements')
            ->assertSee($live->title)
            ->assertSee($live->office)
            ->assertDontSee($draft->title)
            ->assertDontSee($scheduled->title);
    }

    public function test_a_student_can_read_one_announcement(): void
    {
        $announcement = $this->announcement();

        $this->actingAs($this->makeStudent())
            ->get('/student/announcements/'.$announcement->id)
            ->assertOk()
            ->assertSee($announcement->title)
            ->assertSee('Enrollment runs from Monday to Friday')
            ->assertSee('All announcements');
    }

    public function test_an_unpublished_announcement_cannot_be_read(): void
    {
        $draft = $this->announcement(['is_published' => false]);

        $this->actingAs($this->makeStudent())
            ->get('/student/announcements/'.$draft->id)
            ->assertNotFound();
    }

    public function test_an_empty_board_says_so(): void
    {
        $this->actingAs($this->makeStudent())
            ->get('/student/announcements')
            ->assertOk()
            ->assertSee('No announcements yet');
    }

    public function test_the_board_is_students_only(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'registrar']))
            ->get('/student/announcements')
            ->assertForbidden();
    }
}
