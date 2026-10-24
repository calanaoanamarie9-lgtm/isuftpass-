<?php

namespace Tests\Feature;

use App\Models\LibraryService;
use App\Models\User;
use Database\Seeders\LibraryServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryServicesTest extends TestCase
{
    use RefreshDatabase;

    private function makeLibrarian(): User
    {
        return User::factory()->create([
            'role' => 'library',
            'office' => 'Library',
        ]);
    }

    public function test_the_page_lists_every_other_service(): void
    {
        $this->seed(LibraryServiceSeeder::class);

        $response = $this->actingAs($this->makeLibrarian())->get('/library/services');

        $response->assertOk()
            ->assertSee('Other Library Services')
            ->assertSee('Interactive Zone')
            ->assertSee('Current Awareness Service')
            ->assertSee('Referral Service')
            ->assertSee('Multimedia Service')
            ->assertSee('Resource Sharing')
            ->assertSee('Photocopying Service')
            ->assertSee('Indexing Service')
            ->assertSee('Scan and Deliver Service')
            ->assertSee('Bibliographic Listing Service')
            ->assertSee('Search a service');
    }

    public function test_the_page_offers_every_category_as_a_filter(): void
    {
        $this->seed(LibraryServiceSeeder::class);

        $response = $this->actingAs($this->makeLibrarian())->get('/library/services');

        foreach (LibraryService::CATEGORIES as $label) {
            $response->assertSee($label);
        }
    }

    public function test_hidden_services_are_not_on_the_page(): void
    {
        LibraryService::create([
            'name' => 'Archived Service',
            'slug' => 'archived-service',
            'category' => 'access',
            'summary' => 'No longer offered.',
            'description' => 'Retired.',
            'is_active' => false,
        ]);

        $this->actingAs($this->makeLibrarian())
            ->get('/library/services')
            ->assertOk()
            ->assertDontSee('Archived Service');
    }

    public function test_another_office_cannot_open_the_page(): void
    {
        $accountant = User::factory()->create([
            'role' => 'accounting',
            'office' => 'Accounting',
        ]);

        $this->actingAs($accountant)
            ->get('/library/services')
            ->assertForbidden();
    }

    public function test_a_guest_is_sent_to_login(): void
    {
        $this->get('/library/services')->assertRedirect('/login');
    }

    public function test_requirements_are_listed_one_per_line(): void
    {
        $this->seed(LibraryServiceSeeder::class);

        $service = LibraryService::where('slug', 'scan-and-deliver')->firstOrFail();

        $this->assertSame([
            'Title, author and page numbers',
            'Email address or Messenger name',
        ], $service->requirementList());
    }

    public function test_a_service_can_be_added_without_touching_the_page(): void
    {
        $this->seed(LibraryServiceSeeder::class);

        LibraryService::create([
            'name' => 'Thesis Binding',
            'slug' => 'thesis-binding',
            'category' => 'reproduction',
            'summary' => 'Hard binding for approved theses.',
            'description' => 'The library binds approved copies of theses and dissertations.',
            'is_active' => true,
            'sort_order' => 99,
        ]);

        $this->actingAs($this->makeLibrarian())
            ->get('/library/services')
            ->assertOk()
            ->assertSee('Thesis Binding');
    }
}
