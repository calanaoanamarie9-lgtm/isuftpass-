<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrarAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private function makeRegistrar(): User
    {
        return User::factory()->create(['role' => 'registrar']);
    }

    private function makeRegistrarOffice(string $open = '08:00', string $close = '17:00'): Office
    {
        // The migration already seeds a Registrar row (offices.name is unique),
        // so update it rather than insert a duplicate.
        return Office::updateOrCreate(
            ['name' => 'Registrar'],
            ['open_time' => $open, 'close_time' => $close, 'is_active' => true]
        );
    }

    public function test_registrar_can_view_the_office_hours_page(): void
    {
        $this->actingAs($this->makeRegistrar())
            ->get('/registrar/availability')
            ->assertOk()
            ->assertSee('Office Hours')
            ->assertSee('Opens')
            ->assertSee('Closes')
            ->assertSee('Save Office Hours');
    }

    public function test_student_cannot_access_the_office_hours_page(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get('/registrar/availability')
            ->assertForbidden();
    }

    public function test_saving_hours_updates_the_office_open_and_close_time(): void
    {
        $this->makeRegistrarOffice('08:00', '17:00');

        $this->actingAs($this->makeRegistrar())
            ->postJson('/registrar/availability/hours', [
                'open_time' => '09:00',
                'close_time' => '18:00',
            ])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $office = Office::where('name', 'Registrar')->first();

        $this->assertSame('09:00', $office->open_time);
        $this->assertSame('18:00', $office->close_time);
    }

    public function test_closing_time_must_follow_the_opening_time(): void
    {
        $this->makeRegistrarOffice();

        $this->actingAs($this->makeRegistrar())
            ->postJson('/registrar/availability/hours', [
                'open_time' => '17:00',
                'close_time' => '08:00',
            ])
            ->assertJsonValidationErrors('close_time');
    }

    public function test_hours_require_both_bounds(): void
    {
        $this->makeRegistrarOffice();

        $this->actingAs($this->makeRegistrar())
            ->postJson('/registrar/availability/hours', [])
            ->assertJsonValidationErrors(['open_time', 'close_time']);
    }
}
