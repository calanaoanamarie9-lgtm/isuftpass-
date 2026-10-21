<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A page kept in the browser asks for a bundle the last deploy deleted.
 *
 * Vite names every build differently and takes the old file with it, so a
 * cached page can ask for a script that is no longer there. The script
 * 404s, Alpine never starts, and the page looks fine while every button
 * on it stops answering. The layout therefore carries a guard that reloads
 * the page once when Alpine is missing, and says so when even that is not
 * enough.
 */
class AssetGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_signed_in_layout_carries_the_guard(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->get(route('student.appointments.index'))
            ->assertOk()
            ->assertSee('isuftpass:asset-reload', false)
            ->assertSee('__isufstpassReady', false);
    }

    public function test_the_guest_layout_carries_the_guard_too(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('isuftpass:asset-reload', false);
    }
}
